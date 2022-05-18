<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Cache\Wrappers;

/**
 * Checkout Config provider abstract cache wrapper.
 * Used by DI virtual type.
 * @since 3.0.0
 */
class ConfigProviderCacheWrapper implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \Amasty\Checkout\Cache\Type
     */
    private $cacheModel;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $originalClass;

    /**
     * @var bool
     */
    private $isCacheable;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Amasty\Checkout\Api\CacheKeyPartProviderInterface[]
     */
    private $cacheVariators;

    /**
     * @var array
     */
    private $cacheTags = [\Magento\Framework\App\Cache\Type\Config::CACHE_TAG];

    /**
     * @param \Amasty\Checkout\Cache\Type $cacheModel
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param string $originalClass
     * @param \Amasty\Checkout\Api\CacheKeyPartProviderInterface[] $cacheVariators
     * @param bool $isCacheable
     */
    public function __construct(
        \Amasty\Checkout\Cache\Type $cacheModel,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        string $originalClass = '',
        array $cacheVariators = [],
        bool $isCacheable = true
    ) {
        $this->cacheModel = $cacheModel;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->originalClass = $originalClass;
        $this->cacheVariators = $cacheVariators;
        $this->isCacheable = $isCacheable;
    }

    /**
     * Retrieve assoc array of checkout configuration.
     * With cache if applicable.
     *
     * @return array
     */
    public function getConfig()
    {
        if (!$this->isCacheable) {
            return $this->getOriginalObject()->getConfig();
        }
        $data = $this->cacheModel->load($this->getCacheKey());
        if ($data === false) {
            $jsLayout = $this->getOriginalObject()->getConfig();
            $this->cacheModel->save($this->serializer->serialize($jsLayout), $this->getCacheKey(), $this->cacheTags);
        } else {
            $jsLayout = $this->serializer->unserialize($data);
        }

        return $jsLayout;
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        $key = 'config|' . $this->originalClass;
        /** @var \Amasty\Checkout\Api\CacheKeyPartProviderInterface $keyPartObject */
        foreach ($this->cacheVariators as $keyPartObject) {
            $key .= '|' . $keyPartObject->getKeyPart();
        }

        return $key;
    }

    /**
     * @return \Magento\Checkout\Model\ConfigProviderInterface
     */
    private function getOriginalObject()
    {
        return $this->objectManager->get($this->originalClass);
    }
}
