<?php


namespace PAXB\Tests\Mocks;


class ComplexEntity {

    /**
     * @XmlElementWrapper(name="primitives")
     * @XmlElement(name="primitive", type="PAXB\Tests\Mocks\PrimitiveEntity")
     */
    private $primitives;

    /**
     * @param mixed $primitives
     */
    public function setPrimitives($primitives)
    {
        $this->primitives = $primitives;
    }

    /**
     * @return mixed
     */
    public function getPrimitives()
    {
        return $this->primitives;
    }

}