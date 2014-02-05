<?php

/**
 * @XmlElement(name="root")
 */
class SampleEntity {

    /**
     * @XmlElement(name="attribute-value", type="AttributeValueEntity")
     */
    private $nestedEntity;

    private $text;

    /**
     * @XmlElementWrapper(name="number-list")
     */
    private $number = array();

    /**
     * @XmlPhpCollection
     * @XmlElementWrapper(name="one-element-list")
     */
    private $single = array();


    public function __construct($number = array(), $nestedEntity = null, $text = "", $single = array())
    {
        $this->number = $number;
        $this->nestedEntity = $nestedEntity;
        $this->text = $text;
        $this->single = $single;
    }

    /**
     * @return AttributeValueEntity
     */
    public function getNestedEntity()
    {
        return $this->nestedEntity;
    }

    /**
     * @return array
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getSingle()
    {
        return $this->single;
    }

}