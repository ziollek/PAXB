<?php

namespace PAXB\Xml\Binding\Annotations;

/**
 * @Annotation
 */
class XmlElement extends XmlAnnotation {
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type = '';

}