<?php

namespace PAXB\Tests\Xml\Marshalling;

use PAXB\Tests\Mocks\AttributeEntity;
use PAXB\Tests\Mocks\ClassMetadataFactoryMock;
use PAXB\Tests\Mocks\ComplexEntity;
use PAXB\Tests\Mocks\PrimitiveEntity;
use PAXB\Xml\Binding\Metadata\ClassMetadata;
use PAXB\Xml\Binding\Metadata\ClassMetadataFactory;
use PAXB\Xml\Binding\Structure\Attribute;
use PAXB\Xml\Binding\Structure\Element;
use PAXB\Xml\Marshalling\DOMDocumentMarshaller;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass  PAXB\Xml\Marshalling\DOMDocumentMarshaller
 * @covers ::__construct
 * @covers ::<!public>
 */
class DOMDocumentMarshallerTest extends PHPUnit_Framework_TestCase {

    /**
     * @test
     * @covers ::marshall
     */
    public function shouldThrowExceptionForMarshalingPrimitives() {
        $marshaller = new DOMDocumentMarshaller($this->getClassMetadataFactoryMock());

        $this->setExpectedException('\PAXB\Xml\Marshalling\MarshallingException');
        $marshaller->marshall('123');
    }

    /**
     * @test
     * @covers ::marshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForPrimitiveEntity() {

        $expectedString =  <<<EOD
<?xml version="1.0"?>
<primitive-entity><string-field>SomeValue</string-field></primitive-entity>

EOD;

        $entity        = new PrimitiveEntity();
        $entity->setStringField('SomeValue');

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');
        $classMetadata->setName('primitive-entity');
        $classMetadata->addElement('stringField', new Element('string-field', Element::FIELD_SOURCE));

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array('PAXB\Tests\Mocks\PrimitiveEntity' => $classMetadata)
        );

        $marshaller = new DOMDocumentMarshaller($classMetadataFactory);

        $this->assertSame($expectedString, $marshaller->marshall($entity));
    }

    /**
     * @test
     * @covers ::marshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForAttributeEntity() {

        $expectedString =  <<<EOD
<?xml version="1.0"?>
<attribute-entity attr="SampleAttribute"><string-field>SomeValue1</string-field><string-field>SomeValue2</string-field>SampleRootValue</attribute-entity>

EOD;

        $entity = new AttributeEntity();
        $entity->setStringField(array('SomeValue1', 'SomeValue2'));
        $entity->setAttributeField('SampleAttribute');
        $entity->setValueField('SampleRootValue');

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\AttributeEntity');
        $classMetadata->setName('attribute-entity');
        $classMetadata->addAttributes('attributeField', new Attribute('attr', Attribute::FIELD_SOURCE));
        $classMetadata->addElement('stringField', new Element('string-field', Element::FIELD_SOURCE));
        $classMetadata->setValueElement('valueField');

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array('PAXB\Tests\Mocks\AttributeEntity' => $classMetadata)
        );

        $marshaller = new DOMDocumentMarshaller($classMetadataFactory);

        $this->assertSame($expectedString, $marshaller->marshall($entity));
    }

    /**
     * @test
     * @covers ::marshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForAttributeEntityWithNestedCollection() {

        $expectedString =  <<<EOD
<?xml version="1.0"?>
<attribute-entity attr="SampleAttribute"><string-field>SomeValue1</string-field><string-field>SomeValue2</string-field>SampleRootValue</attribute-entity>

EOD;

        $entity = new AttributeEntity();
        $entity->setStringField(new \ArrayIterator(array('SomeValue1', 'SomeValue2')));
        $entity->setAttributeField('SampleAttribute');
        $entity->setValueField('SampleRootValue');

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\AttributeEntity');
        $classMetadata->setName('attribute-entity');
        $classMetadata->addAttributes('attributeField', new Attribute('attr', Attribute::FIELD_SOURCE));
        $classMetadata->addElement('stringField', new Element('string-field', Element::FIELD_SOURCE));
        $classMetadata->setValueElement('valueField');

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array('PAXB\Tests\Mocks\AttributeEntity' => $classMetadata)
        );

        $marshaller = new DOMDocumentMarshaller($classMetadataFactory);

        $this->assertSame($expectedString, $marshaller->marshall($entity));
    }

    /**
     * @test
     * @covers ::marshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForComplexEntity() {

        $expectedString =  <<<EOD
<?xml version="1.0"?>
<complex-entity><primitives><primitive><stringField>First</stringField></primitive><primitive><stringField>Second</stringField></primitive></primitives></complex-entity>

EOD;

        $firstPrimitive = new PrimitiveEntity();
        $firstPrimitive->setStringField('First');
        $secondPrimitive = new PrimitiveEntity();
        $secondPrimitive->setStringField('Second');
        $entity = new ComplexEntity();
        $entity->setPrimitives(array($firstPrimitive, $secondPrimitive));

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\ComplexEntity');
        $classMetadata->setName('complex-entity');
        $classMetadata->addElement('primitives', new Element('primitive', Element::FIELD_SOURCE, ClassMetadata::DEFINED_TYPE, 'PAXB\Tests\Mocks\PrimitiveEntity', 'primitives'));

        $primitiveClassMetadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');
        $primitiveClassMetadata->setName('primitive-entity');
        $primitiveClassMetadata->addElement('stringField', new Element('stringField', ClassMetadata::RUNTIME_TYPE));

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array(
                'PAXB\Tests\Mocks\ComplexEntity' => $classMetadata,
                'PAXB\Tests\Mocks\PrimitiveEntity' => $primitiveClassMetadata,
            )
        );

        $marshaller = new DOMDocumentMarshaller($classMetadataFactory);

        $this->assertSame($expectedString, $marshaller->marshall($entity));
    }

    /**
     * @test
     * @covers ::marshall
     */
    public function shouldOmitEmptyProperties() {

        $expectedString =  <<<EOD
<?xml version="1.0"?>
<complex-entity><primitives/></complex-entity>

EOD;
        $entity = new ComplexEntity();
        $entity->setPrimitives(array());

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\ComplexEntity');
        $classMetadata->setName('complex-entity');
        $classMetadata->addElement('primitives', new Element('primitive', Element::FIELD_SOURCE, ClassMetadata::DEFINED_TYPE, 'PAXB\Tests\Mocks\PrimitiveEntity', 'primitives'));

        $primitiveClassMetadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');
        $primitiveClassMetadata->setName('primitive-entity');
        $primitiveClassMetadata->addElement('stringField', new Element('stringField', ClassMetadata::RUNTIME_TYPE));

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array(
                'PAXB\Tests\Mocks\ComplexEntity' => $classMetadata,
                'PAXB\Tests\Mocks\PrimitiveEntity' => $primitiveClassMetadata,
            )
        );

        $marshaller = new DOMDocumentMarshaller($classMetadataFactory);

        $this->assertSame($expectedString, $marshaller->marshall($entity));
    }


    /**
     * @param array $classMetadataInstances
     *
     * @return ClassMetadataFactory
     */
    private function getClassMetadataFactoryMock($classMetadataInstances = array())
    {
        return new ClassMetadataFactoryMock($classMetadataInstances);
    }
}
