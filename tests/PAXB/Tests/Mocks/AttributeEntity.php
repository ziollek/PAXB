<?php


namespace PAXB\Tests\Mocks;


class AttributeEntity {

    /**
     * @XmlAttribute
     */
    private $attributeField;

    /**
     * @XmlValue
     */
    private $valueField;

    /**
     * @XmlElement
     */
    private $stringField;

    /**
     * @param mixed $stringField
     */
    public function setStringField($stringField)
    {
        $this->stringField = $stringField;
    }

    /**
     * @return mixed
     */
    public function getStringField()
    {
        return $this->stringField;
    }

    /**
     * @param mixed $attributeField
     */
    public function setAttributeField($attributeField)
    {
        $this->attributeField = $attributeField;
    }

    /**
     * @return mixed
     */
    public function getAttributeField()
    {
        return $this->attributeField;
    }

    /**
     * @param mixed $valueField
     */
    public function setValueField($valueField)
    {
        $this->valueField = $valueField;
    }

    /**
     * @return mixed
     */
    public function getValueField()
    {
        return $this->valueField;
    }

}