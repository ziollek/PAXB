<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/SampleEntity.php';
require __DIR__.'/AttributeValueEntity.php';

$sampleEntity = new SampleEntity(
    array(1,2,3),
    new AttributeValueEntity('sample attribure', 'sample value'),
    'Sample text',
    array(1)
);

echo PAXB\Setup::getMarshaller()->marshall($sampleEntity, true);