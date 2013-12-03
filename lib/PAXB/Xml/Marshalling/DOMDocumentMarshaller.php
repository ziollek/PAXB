<?php

namespace PAXB\Xml\Marshalling;

use PAXB\Xml\Binding\Metadata\ClassMetadata;
use PAXB\Xml\Binding\Metadata\ClassMetadataFactory;
use PAXB\Xml\Binding\Structure\Attribute;
use PAXB\Xml\Binding\Structure\Base;
use PAXB\Xml\Binding\Structure\Element;

class DOMDocumentMarshaller {

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @param ClassMetadataFactory $classMetadataFactory
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * @param Object $object
     * @param bool   $format
     *
     * @return string
     */
    public function marshall($object, $format = false)
    {
        $domDocument = new \DOMDocument();
        $domDocument->formatOutput = $format;
        $domDocument->preserveWhiteSpace = $format;

        return $this->marshallObject($domDocument, $object)->saveXML();
    }

    /**
     * @param \DOMDocument $document
     * @param Object       $object
     * @param \DOMElement  $parent
     * @param string       $parentLevelName
     *
     * @return \DOMDocument
     * @throws MarshallingException
     */
    private function marshallObject(\DOMDocument $document, $object, $parent = null, $parentLevelName = null)
    {
        if (!is_object($object)) {
            throw new MarshallingException('Cannot marshall primitive types or arrays');
        }

        $classMetadata = $this->classMetadataFactory->getClassMetadata(get_class($object));

        $elementName = is_null($parentLevelName) ? $classMetadata->getName() : $parentLevelName;

        $element  = $document->createElement($elementName);

        $this->processAttributes($object, $classMetadata, $element);
        $this->processSubElements($document, $object, $classMetadata, $element);
        $this->processValueElement($document, $object, $classMetadata, $element);


        if ($parent == null) {
            $document->appendChild($element);
        } else {
            $parent->appendChild($element);
        }


        return $document;
    }

    /**
     * @param \DOMDocument  $document
     * @param Object        $object
     * @param ClassMetadata $classMetadata
     * @param \DOMElement   $element
     *
     * @throws MarshallingException
     */
    private function processValueElement(\DOMDocument $document, $object, $classMetadata, $element)
    {
        $valueElement = $classMetadata->getValueElement();
        if (!empty($valueElement)) {
            $property = $classMetadata->getReflection()->getProperty($valueElement);
            $property->setAccessible(true);
            $value = $property->getValue($object);

            if (!is_scalar($value)) {
                throw new MarshallingException(
                    'Cannot marshall field ' . $classMetadata->getValueElement() . ' as text node is not scalar'
                );
            }
            $textNode = $document->createTextNode($value);
            $element->appendChild($textNode);
        }
    }

    /**
     * @param Object        $object
     * @param ClassMetadata $classMetadata
     * @param \DOMElement   $element
     *
     * @throws MarshallingException
     */
    private function processAttributes($object, $classMetadata, $element)
    {
        /** @var Attribute $attributeMetadata */
        foreach ($classMetadata->getAttributes() as $propertyName => $attributeMetadata) {
            $attributeValue = $this->getPropertyValue(
                $classMetadata->getReflection()->getProperty($propertyName),
                $attributeMetadata,
                $object
            );

            if (!is_scalar($attributeValue)) {
                throw new MarshallingException(
                    'Cannot marshall field ' . $attributeMetadata->getName() . ' as attribute, value is not scalar'
                );
            }

            $element->setAttribute($attributeMetadata->getName(), $attributeValue);
        }
    }

    /**
     * @param \ReflectionProperty $property
     * @param Base                $baseMetadata
     * @param Object              $object
     *
     * @return mixed
     */
    private function getPropertyValue(\ReflectionProperty $property, Base $baseMetadata, $object)
    {
        if ($baseMetadata->getSource() == Base::FIELD_SOURCE) {
            $property->setAccessible(true);
            return $property->getValue($object);
        } else {
            return $object->{'get'.ucfirst($property->getName())}();
        }
    }

    /**
     * @param \DOMDocument  $document
     * @param Object        $object
     * @param ClassMetadata $classMetadata
     * @param \DOMNode      $element
     */
    private function processSubElements(\DOMDocument $document, $object, $classMetadata, $element)
    {
        /** @var Element $elementMetadata */
        foreach ($classMetadata->getElements() as $propertyName => $elementMetadata) {
            $elementValue = $this->getPropertyValue(
                $classMetadata->getReflection()->getProperty($propertyName),
                $elementMetadata,
                $object
            );

            $this->checkTypeHinting($elementMetadata, $elementValue);
            $this->createAndAppendChild($document, $elementValue, $elementMetadata, $element);
        }
    }

    /**
     * @param Element $elementMetadata
     * @param mixed   $elementValue
     * @throws MarshallingException
     */
    private function checkTypeHinting(Element $elementMetadata, $elementValue)
    {
        if ($this->isTraversable($elementValue)) {
            $nestedValue = null;
            foreach ($elementValue as $nestedValue) {
                break;
            }
            $elementValue = $nestedValue;

        }
        if ($elementMetadata->getType() == ClassMetadata::DEFINED_TYPE && !empty($elementValue)) {
            if (!is_object($elementValue) || get_class($elementValue) !== $elementMetadata->getTypeValue()) {
                throw new MarshallingException(
                    'Cannot marshall field ' . $elementMetadata->getName(
                    ) . ' as type ' . $elementMetadata->getTypeValue(). ' founded type is: '.get_class($elementValue)
                );
            }
        }
    }

    /**
     * @param \DOMDocument $document
     * @param mixed        $elementValue
     * @param Element      $elementMetadata
     * @param \DOMElement  $element
     */
    private function createAndAppendChild(\DOMDocument $document, $elementValue, $elementMetadata, $element)
    {
        $baseElement = $element;
        $elementWrapper = $elementMetadata->getWrapperName();
        if (!empty($elementWrapper)) {
            $nestedElement = $document->createElement($elementWrapper);
            $element->appendChild($nestedElement);
            $baseElement = $nestedElement;
        }

        if ($this->isTraversable($elementValue)) {
            foreach ($elementValue as $singleElementValue) {
                $this->createSubElement($document, $singleElementValue, $elementMetadata, $baseElement);
            }
        } else {
            $this->createSubElement($document, $elementValue, $elementMetadata, $baseElement);
        }
    }

    /**
     * @param \DOMDocument $document
     * @param mixed        $elementValue
     * @param Element      $elementMetadata
     * @param \DOMElement  $element
     */
    private function createSubElement(\DOMDocument $document, $elementValue, $elementMetadata, $element)
    {
        if (is_object($elementValue)) {
            $this->marshallObject($document, $elementValue, $element, $elementMetadata->getName());
        } else {
            if ($elementMetadata->getType() == ClassMetadata::RUNTIME_TYPE) {
                $nestedElement = $document->createElement($elementMetadata->getName());
                $textElement   = $document->createTextNode($elementValue);
                $nestedElement->appendChild($textElement);
                $element->appendChild($nestedElement);
            }
        }
    }

    /**
     * @param mixed $elementValue
     *
     * @return bool
     */
    private function isTraversable($elementValue)
    {
        return is_array($elementValue)
            || $elementValue instanceof \Iterator
            || $elementValue instanceof \IteratorAggregate;
    }

}