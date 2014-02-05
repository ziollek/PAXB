<?php


namespace PAXB\Tests\Mocks;


class PhpCollectionEntity {

    /**
     * @XmlElementWrapper(name="primitives")
     * @XmlPhpCollection
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