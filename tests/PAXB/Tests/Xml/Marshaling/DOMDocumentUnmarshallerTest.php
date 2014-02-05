<?php


namespace PAXB\Tests\Xml\Marshalling;


use PAXB\Tests\Mocks\AttributeEntity;
use PAXB\Tests\Mocks\ClassMetadataFactoryMock;
use PAXB\Tests\Mocks\ComplexEntity;
use PAXB\Tests\Mocks\PhpCollectionEntity;
use PAXB\Tests\Mocks\PrimitiveEntity;
use PAXB\Xml\Binding\Metadata\ClassMetadata;
use PAXB\Xml\Binding\Metadata\ClassMetadataFactory;
use PAXB\Xml\Binding\Structure\Attribute;
use PAXB\Xml\Binding\Structure\Element;
use PAXB\Xml\Marshalling\DOMDocumentUnmarshaller;


/**
 * @coversDefaultClass  PAXB\Xml\Marshalling\DOMDocumentUnmarshaller
 * @covers ::__construct
 * @covers ::<!public>
 */
class DOMDocumentUnmarshallerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     * @covers ::unmarshall
     */
    public function shouldThowExceptionIfMetadataRootNameIsNotEqualDocumentRootName() {
        $inputXml =  <<<EOD
<?xml version="1.0"?>
<primitive-entity><string-field>SomeValue</string-field></primitive-entity>

EOD;

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');
        $classMetadata->setName('other-name');
        $classMetadata->addElement('stringField', new Element('string-field', Element::FIELD_SOURCE));

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array('PAXB\Tests\Mocks\PrimitiveEntity' => $classMetadata)
        );

        $unmarshaller = new DOMDocumentUnmarshaller($classMetadataFactory);

        $this->setExpectedException('PAXB\Xml\Marshalling\UnmarshallingException');
        $unmarshaller->unmarshall($inputXml, 'PAXB\Tests\Mocks\PrimitiveEntity');
    }

    /**
     * @test
     * @covers ::unmarshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForPrimitiveEntity() {
        $expectedEntity = new PrimitiveEntity();
        $expectedEntity->setStringField('SomeValue');

        $inputXml =  <<<EOD
<?xml version="1.0"?>
<primitive-entity><string-field>SomeValue</string-field></primitive-entity>

EOD;


        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');
        $classMetadata->setName('primitive-entity');
        $classMetadata->addElement('stringField', new Element('string-field', Element::FIELD_SOURCE));

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array('PAXB\Tests\Mocks\PrimitiveEntity' => $classMetadata)
        );

        $unmarshaller = new DOMDocumentUnmarshaller($classMetadataFactory);

        $this->assertEquals($expectedEntity, $unmarshaller->unmarshall($inputXml, 'PAXB\Tests\Mocks\PrimitiveEntity'));
    }

    /**
     * @test
     * @covers ::unmarshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForAttributeEntity() {
        $expectedEntity = new AttributeEntity();
        $expectedEntity->setStringField(array('SomeValue1', 'SomeValue2'));
        $expectedEntity->setAttributeField('SampleAttribute');
        $expectedEntity->setValueField('SampleRootValue');

        $inputXml =  <<<EOD
<?xml version="1.0"?>
<attribute-entity attr="SampleAttribute"><string-field>SomeValue1</string-field><string-field>SomeValue2</string-field>SampleRootValue</attribute-entity>

EOD;

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\AttributeEntity');
        $classMetadata->setName('attribute-entity');
        $classMetadata->addAttributes('attributeField', new Attribute('attr', Attribute::FIELD_SOURCE));
        $classMetadata->addElement('stringField', new Element('string-field', Element::FIELD_SOURCE));
        $classMetadata->setValueElement('valueField');

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array('PAXB\Tests\Mocks\AttributeEntity' => $classMetadata)
        );

        $unmarshaller = new DOMDocumentUnmarshaller($classMetadataFactory);

        $this->assertEquals($expectedEntity, $unmarshaller->unmarshall($inputXml, 'PAXB\Tests\Mocks\AttributeEntity'));
    }

    /**
     * @test
     * @covers ::unmarshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForComplexEntity() {
        $firstPrimitive = new PrimitiveEntity();
        $firstPrimitive->setStringField('First');
        $secondPrimitive = new PrimitiveEntity();
        $secondPrimitive->setStringField('Second');
        $expectedEntity = new ComplexEntity();
        $expectedEntity->setPrimitives(array($firstPrimitive, $secondPrimitive));

        $inputXml =  <<<EOD
<?xml version="1.0"?>
<complex-entity><primitives><primitive><stringField>First</stringField></primitive><primitive><stringField>Second</stringField></primitive></primitives></complex-entity>

EOD;

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

        $marshaller = new DOMDocumentUnmarshaller($classMetadataFactory);

        $this->assertEquals($expectedEntity, $marshaller->unmarshall($inputXml, 'PAXB\Tests\Mocks\ComplexEntity'));
    }

    /**
     * @test
     * @covers ::unmarshall
     */
    public function shouldGenerateProperXmlUsingProvidedClassMetadataForPhpCollectionEntity() {
        $primitive = new PrimitiveEntity();
        $primitive->setStringField('First');
        $expectedEntity = new PhpCollectionEntity();
        $expectedEntity->setPrimitives(array($primitive));

        $inputXml =  <<<EOD
<?xml version="1.0"?>
<php-collection-entity><primitives><primitive><stringField>First</stringField></primitive></primitives></php-collection-entity>

EOD;

        $classMetadata = new ClassMetadata('\PAXB\Tests\Mocks\PhpCollectionEntity');
        $classMetadata->setName('php-collection-entity');
        $classMetadata->addElement('primitives', new Element('primitive', Element::FIELD_SOURCE, ClassMetadata::DEFINED_TYPE, 'PAXB\Tests\Mocks\PrimitiveEntity', 'primitives', true));

        $primitiveClassMetadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');
        $primitiveClassMetadata->setName('primitive-entity');
        $primitiveClassMetadata->addElement('stringField', new Element('stringField', ClassMetadata::RUNTIME_TYPE));

        $classMetadataFactory = $this->getClassMetadataFactoryMock(
            array(
                'PAXB\Tests\Mocks\ComplexEntity' => $classMetadata,
                'PAXB\Tests\Mocks\PrimitiveEntity' => $primitiveClassMetadata,
            )
        );

        $marshaller = new DOMDocumentUnmarshaller($classMetadataFactory);

        $this->assertEquals($expectedEntity, $marshaller->unmarshall($inputXml, 'PAXB\Tests\Mocks\ComplexEntity'));
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
