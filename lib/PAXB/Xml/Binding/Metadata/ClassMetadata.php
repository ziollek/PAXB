<?php


namespace PAXB\Xml\Binding\Metadata;


use PAXB\Xml\Binding\Structure\Element;

class ClassMetadata {

    const RUNTIME_TYPE   = 1;
    const DEFINED_TYPE   = 2;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $attributes = array();

    /**
     * @var Element[]
     */
    private $elements = array();

    /**
     * @var string
     */
    private $valueElement = '';

    /**
     * @var \ReflectionClass
     */
    private $reflection = null;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \PAXB\Xml\Binding\Metadata\Element[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return string
     */
    public function getValueElement()
    {
        return $this->valueElement;
    }

    /**
     * @return \ReflectionClass
     */
    public function getReflection()
    {
        if (is_null($this->reflection)) {
            $this->reflection = new \ReflectionClass($this->className);
        }
        return $this->reflection;
    }

    /**
     * @param string                               $fieldName
     * @param \PAXB\Xml\Binding\Structure\Attribute $attribute
     */
    public function addAttributes($fieldName, $attribute)
    {
        $this->attributes[$fieldName] = $attribute;
    }

    /**
     * @param string                             $fieldName
     * @param \PAXB\Xml\Binding\Structure\Element $element
     */
    public function addElement($fieldName, $element)
    {
        $this->elements[$fieldName] = $element;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $valueElement
     */
    public function setValueElement($valueElement)
    {
        $this->valueElement = $valueElement;
    }
}