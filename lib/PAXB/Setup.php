<?php

namespace PAXB;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use PAXB\Xml\Binding\AnnotationLoader;
use PAXB\Xml\Binding\Metadata\ClassMetadataFactory;
use PAXB\Xml\Marshalling\Marshaller;
use PAXB\Xml\Marshalling\Unmarshaller;

/**
 * This class is using only for demo purposes, i highly recommend using DI (ie. Symfony2 container)
 */
class Setup {

    /**
     * @var ClassMetadataFactory
     */
    private static $classMetadataFactory;

    /**
     * @var Marshaller
     */
    private static $marshaller;

    /**
     * @var Unmarshaller
     */
    private static $unmarshaller;


    /**
     * @return Marshaller
     */
    public static function getMarshaller()
    {
        if (self::$marshaller == null) {
            self::$marshaller = new \PAXB\Xml\Marshalling\DOMDocumentMarshaller(
                self::getClassMetadataFactory()
            );
        }

        return self::$marshaller;
    }

    /**
     * @return Unmarshaller
     */
    public static function getUnmarshaller()
    {
        if (self::$unmarshaller == null) {
            self::$unmarshaller = new \PAXB\Xml\Marshalling\DOMDocumentUnmarshaller(
                self::getClassMetadataFactory()
            );
        }

        return self::$unmarshaller;
    }

    /**
     * @return ClassMetadataFactory
     */
    public static function getClassMetadataFactory()
    {
        if (self::$classMetadataFactory == null) {
            $reader = new SimpleAnnotationReader();
            self::loadAnnotationFiles($reader);
            self::$classMetadataFactory = new \PAXB\Xml\Binding\Metadata\CachedClassMetadataFactory(
                new \PAXB\Xml\Binding\Metadata\BaseClassMetadataFactory(
                    new \PAXB\Xml\Binding\AnnotationLoader($reader)
                ),
                self::getCache()
            );
        }

        return self::$classMetadataFactory;
    }

    /**
     * @return Cache
     */
    private static function getCache()
    {
        return new ArrayCache();
    }

    /**
     * @param SimpleAnnotationReader $reader
     */
    private static function loadAnnotationFiles(SimpleAnnotationReader $reader)
    {
        $reader->addNamespace(AnnotationLoader::ANNOTATIONS_NAMESPACE);
        foreach (glob(__DIR__ . '/Xml/Binding/Annotations/*.php') as $annotationFile) {
            AnnotationRegistry::registerFile($annotationFile);
        }
    }
}
