<?php


namespace PAXB\Xml\Binding\Metadata;


interface ClassMetadataFactory {

    /**
     * @param string $className
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($className);
}