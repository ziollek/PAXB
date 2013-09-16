<?php


namespace PAXB\Xml\Binding\Metadata;


use Doctrine\Common\Cache\Cache;

class CachedClassMetadataFactory implements ClassMetadataFactory {
    const CACHE_PREFIX = 'PAXB_MD:';

    /**
     * @var BaseClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(ClassMetadataFactory $classMetadataFactory, Cache $cache)
    {
        $this->classMetadataFactory = $classMetadataFactory;
        $this->cache = $cache;
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($className)
    {
        $cacheKey = self::CACHE_PREFIX . md5($className);
        $classMetadata = $this->cache->fetch($cacheKey);
        if (!($classMetadata instanceof ClassMetadata))  {
            $classMetadata = $this->classMetadataFactory->getClassMetadata($className);
            $this->cache->save($cacheKey, $classMetadata);
        }

        return $classMetadata;
    }


}