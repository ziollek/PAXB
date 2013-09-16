<?php

namespace PAXB\Tests\Xml\Binding;

use PAXB\Xml\Binding\AnnotationLoader;
use PAXB\Xml\Binding\Annotations\XmlAnnotation;
use PAXB\Xml\Binding\Annotations\XmlAttribute;
use PAXB\Xml\Binding\Annotations\XmlElement;
use PAXB\Xml\Binding\Annotations\XmlElementWrapper;
use PAXB\Xml\Binding\Annotations\XmlTransient;
use PAXB\Xml\Binding\Annotations\XmlValue;
use PAXB\Xml\Binding\Metadata\ClassMetadata;
use PAXB\Xml\Binding\Structure\Attribute;
use PAXB\Xml\Binding\Structure\Element;

/**
 * @coversDefaultClass  PAXB\Xml\Binding\AnnotationLoader
 * @covers ::__construct
 * @covers ::<!public>
 */
class AnnotationLoaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldSetElementNameFromClassAnnotations() {
        $expectedName = 'empty-element';
        $reader = $this->getReaderMock(
            array(new XmlElement(array('name' => '' . $expectedName . '')))
        );

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\EmptyEntity');

        $annotationLoader = new AnnotationLoader($reader);
        $annotationLoader->loadClassMetadata($metadata);

        $this->assertEquals($expectedName, $metadata->getName());
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldSetClassNameAsRootNameIfAnnotationNotExists() {

        $expectedName = 'EmptyEntity';
        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\EmptyEntity');

        $annotationLoader = new AnnotationLoader($this->getReaderMock());
        $annotationLoader->loadClassMetadata($metadata);

        $this->assertEquals($expectedName, $metadata->getName());
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldSetMetadataElementFromFieldAnnotation() {
        $expectedElement = new Element(
            'string-element',Element::FIELD_SOURCE, ClassMetadata::DEFINED_TYPE, '\PAXB\Tests\Mocks\EmptyEntity', 'wrapper-element'
        );

        $reader = $this->getReaderMock(
            array(),
            array(
                'stringField-1' => new XmlElement(array('name' => 'string-element', 'type' => '\PAXB\Tests\Mocks\EmptyEntity')),
                'stringField-2' => new XmlElementWrapper(array('name' => 'wrapper-element'))
                )
        );

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $annotationLoader = new AnnotationLoader($reader);
        $annotationLoader->loadClassMetadata($metadata);

        $this->assertContains($expectedElement, $metadata->getElements(), '', false, false);
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldThrowExceptionIfXmlWrapperElementHasNoName() {
        $reader = $this->getReaderMock(
            array(),
            array(
                'stringField-2' => new XmlElementWrapper(array())
            )
        );

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $annotationLoader = new AnnotationLoader($reader);

        $this->setExpectedException('\Doctrine\Common\Annotations\AnnotationException');
        $annotationLoader->loadClassMetadata($metadata);
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldSetDefaultMetadataElement() {
        $expectedElement = new Element(
            'stringField',Element::FIELD_SOURCE, ClassMetadata::RUNTIME_TYPE
        );
        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $annotationLoader = new AnnotationLoader($this->getReaderMock());
        $annotationLoader->loadClassMetadata($metadata);

        $this->assertContains($expectedElement, $metadata->getElements(), '', false, false);
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldOmitFieldsWithTransientAnnotation() {

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $reader = $this->getReaderMock(
            array(),
            array(new XmlTransient(array()))
        );

        $annotationLoader = new AnnotationLoader($reader);
        $annotationLoader->loadClassMetadata($metadata);

        $this->assertCount(0, $metadata->getElements());
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldSetAttributeMetadataFromFieldAnnotation() {

        $expectedAttribute = new Attribute(
            'string-attribute', Element::FIELD_SOURCE
        );

        $reader = $this->getReaderMock(
            array(),
            array(
                'stringField' => new XmlAttribute(array('name' => 'string-attribute')),
            )
        );

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $annotationLoader = new AnnotationLoader($reader);
        $annotationLoader->loadClassMetadata($metadata);

        $this->assertContains($expectedAttribute, $metadata->getAttributes(), '', false, false);
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldSetValueMetadataFromFieldAnnotation() {

        $expectedValue = 'stringField';

        $reader = $this->getReaderMock(
            array(),
            array(
                'stringField' => new XmlValue(array()),
            )
        );

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $annotationLoader = new AnnotationLoader($reader);
        $annotationLoader->loadClassMetadata($metadata);

        $this->assertSame($expectedValue, $metadata->getValueElement());
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     */
    public function shouldThrowExceptionIfXmlValueIsNotUnique() {
        $reader = $this->getReaderMock(
            array(),
            array(
                'stringField-1' => new XmlValue(array()),
                'stringField-2' => new XmlValue(array()),
            )
        );

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $annotationLoader = new AnnotationLoader($reader);

        $this->setExpectedException('\Doctrine\Common\Annotations\AnnotationException');
        $annotationLoader->loadClassMetadata($metadata);
    }

    /**
     * @return array
     */
    public function notAllowedAnnotationCombinations()
    {
        return array(
            array(new XmlElement(array()), new XmlAttribute(array())),
            array(new XmlElement(array()), new XmlValue(array())),
            array(new XmlValue(array()), new XmlAttribute(array())),
            array(new XmlElement(array()), new XmlTransient(array())),
        );
    }

    /**
     * @test
     * @covers ::loadClassMetadata
     * @dataProvider notAllowedAnnotationCombinations
     */
    public function shouldThrowExceptionIfUsingSimultaneouslyNotAllowedAnnotations($annotationFirst, $annotationSecond) {
        $reader = $this->getReaderMock(
            array(),
            array(
                $annotationFirst,
                $annotationSecond
            )
        );

        $metadata = new ClassMetadata('\PAXB\Tests\Mocks\PrimitiveEntity');

        $annotationLoader = new AnnotationLoader($reader);

        $this->setExpectedException('\Doctrine\Common\Annotations\AnnotationException');
        $annotationLoader->loadClassMetadata($metadata);
    }


    /**
     * @param array $classAnnotations
     * @param array $propertyAnnotations
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getReaderMock($classAnnotations = array(), $propertyAnnotations = array())
    {
        $reader = $this->getMockBuilder('\Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        if (count($classAnnotations) > 0) {
            $reader->expects($this->any())
                ->method('getClassAnnotations')
                ->will($this->returnValue($classAnnotations));
        }

        if (count($propertyAnnotations) > 0) {
            $reader->expects($this->any())
                ->method('getPropertyAnnotations')
                ->will($this->returnValue($propertyAnnotations));
        }

        return $reader;
    }
}
