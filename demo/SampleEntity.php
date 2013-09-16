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


    public function __construct($number = array(), $nestedEntity = null, $text = "")
    {
        $this->number = $number;
        $this->nestedEntity = $nestedEntity;
        $this->text = $text;
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


}