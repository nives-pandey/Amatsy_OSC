<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Cache\Wrappers;

use Amasty\Checkout\Api\CacheKeyPartProviderInterface;
use Amasty\Checkout\Model\Optimization\LayoutJsDiffProcessor;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Checkout layout processors abstract cache wrapper.
 * Used by DI virtual type.
 *
 * @since 3.0.0
 * @since 3.0.10 cache storage improve: store only difference not a whole layout.
 */
class LayoutProcessorCacheWrapper implements LayoutProcessorInterface
{
    /**
     * @var \Amasty\Checkout\Cache\Type
     */
    private $cacheModel;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $processorClassName;

    /**
     * @var bool
     */
    private $isCacheable;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheKeyPartProviderInterface[]
     */
    private $cacheVariators;

    /**
     * @var LayoutJsDiffProcessor
     */
    private $arrayDiffProcessor;

    /**
     * @var array
     */
    private $cacheTags = [\Magento\Framework\App\Cache\Type\Config::CACHE_TAG];

    /**
     * @param \Amasty\Checkout\Cache\Type $cacheModel
     * @param ObjectManagerInterface $objectManager
     * @param SerializerInterface $serializer
     * @param LayoutJsDiffProcessor $arrayDiffProcessor
     * @param string $processorClass
     * @param CacheKeyPartProviderInterface[] $cacheVariators
     * @param bool $isCacheable
     */
    public function __construct(
        \Amasty\Checkout\Cache\Type $cacheModel,
        ObjectManagerInterface $objectManager,
        SerializerInterface $serializer,
        LayoutJsDiffProcessor $arrayDiffProcessor,
        string $processorClass = \Magento\Checkout\Block\Checkout\LayoutProcessor::class,
        array $cacheVariators = [],
        bool $isCacheable = true
    ) {
        $this->cacheModel = $cacheModel;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->processorClassName = $processorClass;
        $this->cacheVariators = $cacheVariators;
        $this->isCacheable = $isCacheable;
        $this->arrayDiffProcessor = $arrayDiffProcessor;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout): array
    {
        if (!$this->getIsCacheable()) {
            return $this->getProcessorObject()->process($jsLayout);
        }

        $cacheKey = $this->getCacheKey();

        $diffData = $this->getCacheData($cacheKey);
        if ($diffData === null) {
            $originLayout = $this->hardCopyArray($jsLayout);
            $jsLayout = $this->getProcessorObject()->process($jsLayout);
            $diffData = $this->arrayDiffProcessor->createFlatDiff($originLayout, $jsLayout);
            $this->saveCache($diffData);
        } else {
            $jsLayout = $this->processCache($diffData, $jsLayout);
        }

        return $jsLayout;
    }

    /**
     * Clone array.
     * Fix PHP bug when array behaves like deep linked array.
     *
     * @param array $array
     *
     * @return array
     */
    private function hardCopyArray(array $array): array
    {
        $copyArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $copyArray[$key] = $this->hardCopyArray($array[$key]);
            } else {
                $copyArray[$key] = $value;
            }
        }

        return $copyArray;
    }

    /**
     * @param string $cacheKey
     *
     * @return array|null
     */
    public function getCacheData(string $cacheKey): ?array
    {
        $data = $this->cacheModel->load($cacheKey);

        if ($data === false) {
            return null;
        }

        return $this->serializer->unserialize($data);
    }

    /**
     * @param array $data
     */
    protected function saveCache(array $data): void
    {
        $this->cacheModel->save($this->serializer->serialize($data), $this->getCacheKey(), $this->cacheTags);
    }

    /**
     * @param array $diffData
     * @param array $jsLayout
     *
     * @return array
     */
    protected function processCache(array $diffData, array $jsLayout): array
    {
        return $this->arrayDiffProcessor->applyDiffToArray($jsLayout, $diffData);
    }

    /**
     * @return bool
     */
    public function getIsCacheable(): bool
    {
        return $this->isCacheable;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        $key = 'layoutProc|' . $this->processorClassName;
        /** @var CacheKeyPartProviderInterface $keyPartObject */
        foreach ($this->cacheVariators as $keyPartObject) {
            $key .= '|' . $keyPartObject->getKeyPart();
        }

        return $key;
    }

    /**
     * @return LayoutProcessorInterface
     */
    private function getProcessorObject(): LayoutProcessorInterface
    {
        return $this->objectManager->get($this->processorClassName);
    }
}
