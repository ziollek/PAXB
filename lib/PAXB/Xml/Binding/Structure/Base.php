<?php


namespace PAXB\Xml\Binding\Structure;


class Base {

    const GETTER_SOURCE = 1;
    const FIELD_SOURCE  = 2;

    protected $name;
    protected $source;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }


}