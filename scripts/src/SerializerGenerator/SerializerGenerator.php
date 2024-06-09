<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Scripts\SerializerGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\PropertyInfo\Extractor\ConstructorExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Generates symfony serializer php class based on list of DTOs for fast serialization.
 *
 * @internal
 *
 * @psalm-api
 */
final class SerializerGenerator
{
    private const SCALAR_TYPES = [
        Type::BUILTIN_TYPE_BOOL,
        Type::BUILTIN_TYPE_FLOAT,
        Type::BUILTIN_TYPE_INT,
        Type::BUILTIN_TYPE_STRING,
    ];
    private const SCALAR_TYPES_DEFAULTS = [
        Type::BUILTIN_TYPE_BOOL => 'false',
        Type::BUILTIN_TYPE_FLOAT => '.0',
        Type::BUILTIN_TYPE_INT => '0',
        Type::BUILTIN_TYPE_STRING => "''",
    ];

    private readonly PropertyInfoExtractorInterface $propertyInfoExtractor;

    public function __construct()
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $this->propertyInfoExtractor = new PropertyInfoExtractor(
            listExtractors: [
                $reflectionExtractor,
            ],
            typeExtractors: [
                new ConstructorExtractor([$phpDocExtractor, $reflectionExtractor]),
                $phpDocExtractor,
            ]
        );
    }

    /**
     * Scan all objects from folder and generate serializer for fast serialization.
     */
    public function generate(\SplFileInfo $dtosFolder, \SplFileInfo $targetFile, string $targetNamespace): void
    {
        $classes = $this->collectClassesFromFolder($dtosFolder);
        $descriptions = $this->getClassesDescriptions($classes);
        $phpFile = $this->buildFile(
            $descriptions,
            $targetNamespace,
            $targetFile->getBasename(".{$targetFile->getExtension()}")
        );

        file_put_contents(
            $targetFile->getPathname(),
            (new PsrPrinter())->printFile($phpFile),
        );
    }

    /**
     * @param array<string, ClassDescription> $descriptions
     */
    private function buildFile(array $descriptions, string $namespace, string $className): PhpFile
    {
        $phpFile = new PhpFile();
        $phpFile->setStrictTypes();

        $ns = $phpFile->addNamespace($namespace)
            ->addUse(NormalizerInterface::class)
            ->addUse(InvalidArgumentException::class)
            ->addUse(DenormalizerInterface::class);
        foreach ($descriptions as $description) {
            $ns->addUse($description->className);
        }

        $class = $ns->addClass($className)
            ->setFinal()
            ->addImplement(DenormalizerInterface::class)
            ->addImplement(NormalizerInterface::class);

        $this->addSupportsNormalization($class, $descriptions);
        $this->addNormalize($class, $descriptions);
        $this->addSupportsDenormalization($class, $descriptions);
        $this->addDenormalize($class, $descriptions);
        $this->addGetSupportedTypes($class, $descriptions);

        foreach ($descriptions as $description) {
            $this->addEntityNormalize($class, $description, $descriptions);
        }
        foreach ($descriptions as $description) {
            $this->addEntityDenormalize($class, $description, $descriptions);
        }

        return $phpFile;
    }

    /**
     * @param ClassDescription[] $descriptions
     */
    private function addSupportsNormalization(ClassType $class, array $descriptions): void
    {
        $body = '';
        foreach ($descriptions as $description) {
            if ('' !== $body) {
                $body .= "\n    || ";
            }
            $body .= "\$data instanceof {$description->shortClassName}";
        }
        $body = "return {$body};";

        $method = $class->addMethod('supportsNormalization')
            ->setReturnType('bool')
            ->addComment("{@inheritDoc}\n")
            ->setVisibility('public')
            ->setBody($body);
        $method->addParameter('data')->setType('mixed');
        $method->addParameter('format', new Literal('null'))->setNullable()->setType('string');
        $method->addParameter('context', new Literal('[]'))->setType('array');
    }

    /**
     * @param array<string, ClassDescription> $descriptions
     */
    private function addNormalize(ClassType $class, array $descriptions): void
    {
        $body = '';
        foreach ($descriptions as $description) {
            if ('' !== $body) {
                $body .= ' else';
            }
            $body .= "if (\$object instanceof {$description->shortClassName}) {\n";
            $body .= "    return \$this->normalize{$description->shortClassName}(\$object);\n";
            $body .= '}';
        }
        $body .= "\n\nthrow new InvalidArgumentException(\"Can't normalize provided data\");";

        $method = $class->addMethod('normalize')
            ->setReturnType('array')
            ->addComment("{@inheritDoc}\n")
            ->setVisibility('public')
            ->setBody($body);
        $method->addParameter('object')->setType('mixed');
        $method->addParameter('format', new Literal('null'))->setNullable()->setType('string');
        $method->addParameter('context', new Literal('[]'))->setType('array');
    }

    /**
     * @param array<string, ClassDescription> $descriptions
     */
    private function addEntityNormalize(ClassType $class, ClassDescription $description, array $descriptions): void
    {
        $body = '';
        foreach ($description->properties as $property => $definition) {
            if (!isset($definition[0])) {
                continue;
            }
            $type = $definition[0];
            $propertyKey = $this->camelCaseToSnakeCase($property);
            $builtInType = $type->getBuiltinType();
            if (\in_array($builtInType, self::SCALAR_TYPES)) {
                $body .= "    '{$propertyKey}' => \$object->{$property},\n";
            } elseif ($type->isCollection()) {
                $valueType = $type->getCollectionValueTypes()[0] ?? null;
                $builtInValueType = $valueType?->getBuiltinType();
                $valueDescription = $descriptions[(string) $valueType?->getClassName()] ?? null;
                if (\in_array($builtInValueType, self::SCALAR_TYPES)) {
                    $body .= "    '{$propertyKey}' => \$object->{$property},\n";
                } elseif ($valueDescription) {
                    $body .= "    '{$propertyKey}' => array_map(";
                    $body .= "fn ({$valueDescription->shortClassName} \$val): array => \$this->normalize{$valueDescription->shortClassName}(\$val),";
                    $body .= " \$object->{$property}";
                    $body .= "),\n";
                }
            }
        }
        $body = "return [\n{$body}];";

        $method = $class->addMethod("normalize{$description->shortClassName}")
            ->setReturnType('array')
            ->setVisibility('private')
            ->setBody($body);
        $method->addParameter('object')->setType($description->className);
    }

    /**
     * @param ClassDescription[] $descriptions
     */
    private function addSupportsDenormalization(ClassType $class, array $descriptions): void
    {
        $body = '';
        foreach ($descriptions as $description) {
            if ('' !== $body) {
                $body .= "\n    || ";
            }
            $body .= "\$type === {$description->shortClassName}::class";
        }
        $body = "\nreturn {$body};";

        $method = $class->addMethod('supportsDenormalization')
            ->setReturnType('bool')
            ->addComment("{@inheritDoc}\n")
            ->setVisibility('public')
            ->setBody($body);
        $method->addParameter('data')->setType('mixed');
        $method->addParameter('type')->setType('string');
        $method->addParameter('format', new Literal('null'))->setNullable()->setType('string');
        $method->addParameter('context', new Literal('[]'))->setType('array');
    }

    /**
     * @param array<string, ClassDescription> $descriptions
     */
    private function addDenormalize(ClassType $class, array $descriptions): void
    {
        $conditions = '';
        foreach ($descriptions as $description) {
            if ('' !== $conditions) {
                $conditions .= ' else';
            }
            $conditions .= "if (\$type === {$description->shortClassName}::class) {\n";
            $conditions .= "    return \$this->denormalize{$description->shortClassName}(\$data);\n";
            $conditions .= '}';
        }
        $body = "if (!is_array(\$data)) {\n";
        $body .= "    throw new InvalidArgumentException(\"Can't denormalize provided data\");\n";
        $body .= "}\n\n";
        $body .= "{$conditions}\n\n";
        $body .= "throw new InvalidArgumentException(\"Can't denormalize provided type\");";

        $method = $class->addMethod('denormalize')
            ->setReturnType('mixed')
            ->addComment("{@inheritDoc}\n")
            ->setVisibility('public')
            ->setBody($body);
        $method->addParameter('data')->setType('mixed');
        $method->addParameter('type')->setType('string');
        $method->addParameter('format', new Literal('null'))->setNullable()->setType('string');
        $method->addParameter('context', new Literal('[]'))->setType('array');
    }

    /**
     * @param array<string, ClassDescription> $descriptions
     */
    private function addEntityDenormalize(ClassType $class, ClassDescription $description, array $descriptions): void
    {
        $body = '';
        foreach ($description->properties as $property => $definition) {
            if (!isset($definition[0])) {
                continue;
            }
            $type = $definition[0];
            $propertyKey = $this->camelCaseToSnakeCase($property);
            $builtInType = $type->getBuiltinType();
            if (!$type->isCollection() && \in_array($builtInType, self::SCALAR_TYPES)) {
                $default = self::SCALAR_TYPES_DEFAULTS[$builtInType];
                $body .= "    ($builtInType) (\$data['{$propertyKey}'] ?? {$default}),\n";
            } elseif ($type->isCollection()) {
                $valueType = $type->getCollectionValueTypes()[0] ?? null;
                $builtInValueType = $valueType?->getBuiltinType();
                $valueDescription = $descriptions[(string) $valueType?->getClassName()] ?? null;
                if (\in_array($builtInValueType, self::SCALAR_TYPES)) {
                    $body .= '    array_map(';
                    $body .= "fn (mixed \$val): {$builtInValueType} => ({$builtInValueType}) \$val,";
                    $body .= " (array) (\$data['{$propertyKey}'] ?? [])),\n";
                } elseif ($valueDescription) {
                    $body .= '    array_map(';
                    $body .= "fn (array \$val): {$valueDescription->shortClassName} => \$this->denormalize{$valueDescription->shortClassName}(\$val),";
                    $body .= " (array) (\$data['{$propertyKey}'] ?? [])),\n";
                }
            }
        }
        $body = "return new {$description->shortClassName}(\n{$body});";

        $method = $class->addMethod("denormalize{$description->shortClassName}")
            ->setReturnType($description->className)
            ->setVisibility('private')
            ->setBody($body);
        $method->addParameter('data')->setType('array');
    }

    /**
     * @param ClassDescription[] $descriptions
     */
    private function addGetSupportedTypes(ClassType $class, array $descriptions): void
    {
        $body = '';
        foreach ($descriptions as $description) {
            $body .= "\n    {$description->shortClassName}::class => true,";
        }
        $body = "return [{$body}\n];";

        $method = $class->addMethod('getSupportedTypes')
            ->setReturnType('array')
            ->addComment("{@inheritDoc}\n")
            ->setVisibility('public')
            ->setBody($body);
        $method->addParameter('format')->setNullable()->setType('string');
    }

    /**
     * @param string[] $classes
     *
     * @psalm-param class-string[] $classes
     *
     * @return array<string, ClassDescription>
     */
    private function getClassesDescriptions(array $classes): array
    {
        $result = [];

        foreach ($classes as $class) {
            $propertiesDescriptions = [];
            $properties = $this->propertyInfoExtractor->getProperties($class) ?? [];
            foreach ($properties as $property) {
                $propertiesDescriptions[$property] = array_filter($this->propertyInfoExtractor->getTypes($class, $property));
            }
            $explodedArrayName = explode('\\', $class);
            $result[$class] = new ClassDescription(
                $class,
                end($explodedArrayName),
                $propertiesDescriptions
            );
        }

        return $result;
    }

    /**
     * @return string[]
     *
     * @psalm-return class-string[]
     */
    private function collectClassesFromFolder(\SplFileInfo $dtosFolder): array
    {
        $classes = [];

        if (!$dtosFolder->isDir()) {
            return $classes;
        }

        $directoryIterator = new \RecursiveDirectoryIterator(
            $dtosFolder->getRealPath(),
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        /** @var iterable<\SplFileInfo> */
        $filesIterator = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($filesIterator as $file) {
            if ('php' === strtolower($file->getExtension())) {
                $classes = array_merge($classes, $this->getFQCNsFromFile($file));
            }
        }

        return $classes;
    }

    /**
     * @return string[]
     *
     * @psalm-return class-string[]
     */
    private function getFQCNsFromFile(\SplFileInfo $phpFile): array
    {
        $classes = [];
        $tokens = \PhpToken::tokenize(file_get_contents($phpFile->getRealPath()));
        $namespace = '';

        for ($i = 0; $i < \count($tokens); ++$i) {
            if ('T_NAMESPACE' === $tokens[$i]->getTokenName()) {
                for ($j = $i + 1; $j < \count($tokens); ++$j) {
                    if ('T_NAME_QUALIFIED' === $tokens[$j]->getTokenName()) {
                        $namespace = $tokens[$j]->text;
                        break;
                    }
                }
            }
            if ('T_CLASS' === $tokens[$i]->getTokenName()) {
                for ($j = $i + 1; $j < \count($tokens); ++$j) {
                    if ('T_WHITESPACE' === $tokens[$j]->getTokenName()) {
                        continue;
                    }
                    if ('T_STRING' === $tokens[$j]->getTokenName()) {
                        /** @psalm-var class-string */
                        $class = $namespace . '\\' . $tokens[$j]->text;
                        $classes[] = $class;
                    } else {
                        break;
                    }
                }
            }
        }

        return $classes;
    }

    private function camelCaseToSnakeCase(string $camelCase): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCase));
    }
}
