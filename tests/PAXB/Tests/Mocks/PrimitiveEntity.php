<?php

namespace PAXB\Tests\Mocks;

class PrimitiveEntity {

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


}