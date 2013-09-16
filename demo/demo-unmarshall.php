<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/SampleEntity.php';
require __DIR__.'/AttributeValueEntity.php';

$sampleEntity = PAXB\Setup::getUnmarshaller()->unmarshall(file_get_contents(__DIR__.'/sample.xml'), 'SampleEntity');

var_dump($sampleEntity);