<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Scripts\SerializerGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use SuareSu\PyrusClient\Scripts\DTO\ClassDescription;
use SuareSu\PyrusClient\Scripts\Helper\CaseHelper;
use SuareSu\PyrusClient\Scripts\Helper\PhpFileHelper;
use SuareSu\PyrusClient\Scripts\Helper\PhpLineHelper;
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
        $classes = PhpFileHelper::getFQCNsFromFolder($dtosFolder);
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
        $conditions = array_map(
            fn (ClassDescription $d): string => '$data instanceof ' . $d->shortClassName,
            $descriptions
        );
        $body = PhpLineHelper::return(implode("\n    || ", $conditions));

        $method = $class->addMethod('supportsNormalization')
            ->setReturnType('bool')
            ->addComment('{@inheritDoc}')
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
        $conditions = array_map(
            fn (ClassDescription $d): string => PhpLineHelper::if(
                '$object instanceof ' . $d->shortClassName,
                PhpLineHelper::return('$this->normalize' . $d->shortClassName . '($object)')
            ),
            $descriptions
        );
        $body = implode(' else', $conditions);
        $body .= PhpLineHelper::skipLine();
        $body .= PhpLineHelper::throwException('InvalidArgumentException', "Can't normalize provided data");

        $method = $class->addMethod('normalize')
            ->setReturnType('array')
            ->addComment('{@inheritDoc}')
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
        $items = [];
        foreach ($description->properties as $property => $definition) {
            if (!isset($definition[0])) {
                continue;
            }
            $type = $definition[0];
            $propertyKey = CaseHelper::camelToSnake($property);
            $propertyValue = null;
            $builtInType = $type->getBuiltinType();
            if ($type->isCollection()) {
                $valueType = $type->getCollectionValueTypes()[0] ?? null;
                $builtInValueType = $valueType?->getBuiltinType();
                $valueDescription = $descriptions[(string) $valueType?->getClassName()] ?? null;
                if (\in_array($builtInValueType, self::SCALAR_TYPES)) {
                    $propertyValue = "\$object->{$property}";
                } elseif ($valueDescription) {
                    $propertyValue = 'array_map(';
                    $propertyValue .= "fn ({$valueDescription->shortClassName} \$val): array => \$this->normalize{$valueDescription->shortClassName}(\$val),";
                    $propertyValue .= " \$object->{$property}";
                    $propertyValue .= ')';
                }
            } elseif (\in_array($builtInType, self::SCALAR_TYPES)) {
                $propertyValue = "\$object->{$property}";
            }
            if (null !== $propertyValue) {
                $items[] = "'{$propertyKey}' => {$propertyValue}";
            }
        }

        $body = PhpLineHelper::return(PhpLineHelper::array($items));

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
        $conditions = array_map(
            fn (ClassDescription $d): string => "\$type === {$d->shortClassName}::class",
            $descriptions
        );
        $body = PhpLineHelper::return(implode("\n    || ", $conditions));

        $method = $class->addMethod('supportsDenormalization')
            ->setReturnType('bool')
            ->addComment('{@inheritDoc}')
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
            $conditions .= PhpLineHelper::if(
                "\$type === {$description->shortClassName}::class",
                PhpLineHelper::return("\$this->denormalize{$description->shortClassName}(\$data)")
            );
        }

        $body = PhpLineHelper::if(
            '!is_array($data)',
            PhpLineHelper::throwException('InvalidArgumentException', "Can't denormalize provided data")
        );
        $body .= PhpLineHelper::skipLine();
        $body .= $conditions;
        $body .= PhpLineHelper::skipLine();
        $body .= PhpLineHelper::throwException('InvalidArgumentException', "Can't denormalize provided type");

        $method = $class->addMethod('denormalize')
            ->setReturnType('mixed')
            ->addComment('{@inheritDoc}')
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
        $body = PhpLineHelper::NEW_LINE;
        foreach ($description->properties as $property => $definition) {
            if (!isset($definition[0])) {
                continue;
            }
            $type = $definition[0];
            $propertyKey = CaseHelper::camelToSnake($property);
            $propertyValue = null;
            $builtInType = $type->getBuiltinType();
            if ($type->isCollection()) {
                $valueType = $type->getCollectionValueTypes()[0] ?? null;
                $builtInValueType = $valueType?->getBuiltinType();
                $valueDescription = $descriptions[(string) $valueType?->getClassName()] ?? null;
                if (\in_array($builtInValueType, self::SCALAR_TYPES)) {
                    $propertyValue = 'array_map(';
                    $propertyValue .= "fn (mixed \$val): {$builtInValueType} => ({$builtInValueType}) \$val,";
                    $propertyValue .= " (array) (\$data['{$propertyKey}'] ?? []))";
                } elseif ($valueDescription) {
                    $propertyValue = 'array_map(';
                    $propertyValue .= "fn (array \$val): {$valueDescription->shortClassName} => \$this->denormalize{$valueDescription->shortClassName}(\$val),";
                    $propertyValue .= " (array) (\$data['{$propertyKey}'] ?? []))";
                }
            } elseif (\in_array($builtInType, self::SCALAR_TYPES)) {
                $default = self::SCALAR_TYPES_DEFAULTS[$builtInType];
                $propertyValue = "($builtInType) (\$data['{$propertyKey}'] ?? {$default})";
            }
            if (null !== $propertyValue) {
                $body .= PhpLineHelper::line($propertyValue, 1, false) . PhpLineHelper::COMMA . PhpLineHelper::NEW_LINE;
            }
        }

        $body = PhpLineHelper::return("new {$description->shortClassName}({$body})");

        $method = $class->addMethod("denormalize{$description->shortClassName}")
            ->addComment('@psalm-suppress MixedArgumentTypeCoercion')
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
        $lines = array_map(
            fn (ClassDescription $d): string => "{$d->shortClassName}::class => true",
            $descriptions
        );
        $body = PhpLineHelper::return(PhpLineHelper::array($lines));

        $method = $class->addMethod('getSupportedTypes')
            ->setReturnType('array')
            ->addComment('{@inheritDoc}')
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
}
