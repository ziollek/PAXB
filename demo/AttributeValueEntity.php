<?php


class AttributeValueEntity {

    /**
     * @XmlAttribute
     */
    private $attribute;

    /**
     * @XmlElement
     */
    private $value;

    /**
     * @param string $attribute
     * @param string $value
     */
    public function __construct($attribute = "", $value = "")
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}