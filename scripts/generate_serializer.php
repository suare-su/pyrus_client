<?php

declare(strict_types=1);

use SuareSu\PyrusClient\Scripts\SerializerGenerator\SerializerGenerator;

require __DIR__ . '/../vendor/autoload.php';

(new SerializerGenerator())->generate(
    new SplFileInfo(__DIR__ . '/../src/Entity'),
    new SplFileInfo(__DIR__ . '/../src/DataConverter/EntityConverter.php'),
    "SuareSu\PyrusClient\DataConverter"
);
