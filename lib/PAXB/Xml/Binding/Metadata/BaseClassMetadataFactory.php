<?php


namespace PAXB\Xml\Binding\Metadata;

use PAXB\Xml\Binding\AnnotationLoader;

class BaseClassMetadataFactory implements ClassMetadataFactory {

    /**
     * @var AnnotationLoader
     */
    private $loader;

    /**
     * @param AnnotationLoader $loader
     */
    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($className)
    {
        $metadata = new ClassMetadata($className);

        $this->loader->loadClassMetadata($metadata);

        return $metadata;
    }
}


