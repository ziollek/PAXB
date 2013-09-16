<?php

namespace PAXB\Xml\Binding\Structure;


class Attribute extends Base {


    public function __construct($name, $source)
    {
        $this->name = $name;
        $this->source = $source;
    }


}