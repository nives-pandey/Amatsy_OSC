<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Cache\CachedLayoutProcessor;

use Amasty\Checkout\Api\CacheKeyPartProviderInterface;
use Amasty\Checkout\Cache\CachedLayoutProcessor\AddressFormAttributes\DefaultAttributeValueUpdate;
use Amasty\Checkout\Cache\Wrappers\LayoutProcessorCacheWrapper;
use Amasty\Checkout\Model\Optimization\LayoutJsDiffProcessor;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Address Form Attributes layout cache wrapper.
 * Update dynamical default attributes value for logged in customer.
 *
 * @since 3.0.10
 */
class AddressFormAttributes extends LayoutProcessorCacheWrapper implements LayoutProcessorInterface
{
    const ADRESS_FORM_NAMES = ['shipping-address-fieldset', 'form-fields'];

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var DefaultAttributeValueUpdate
     */
    private $attributeValueUpdate;

    /**
     * @param \Amasty\Checkout\Cache\Type $cacheModel
     * @param ObjectManagerInterface $objectManager
     * @param SerializerInterface $serializer
     * @param LayoutJsDiffProcessor $arrayDiffProcessor
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param DefaultAttributeValueUpdate $attributeValueUpdate
     * @param string $processorClass
     * @param CacheKeyPartProviderInterface[] $cacheVariators
     * @param bool $isCacheable
     */
    public function __construct(
        \Amasty\Checkout\Cache\Type $cacheModel,
        ObjectManagerInterface $objectManager,
        SerializerInterface $serializer,
        LayoutJsDiffProcessor $arrayDiffProcessor,
        \Magento\Framework\App\Http\Context $httpContext,
        DefaultAttributeValueUpdate $attributeValueUpdate,
        string $processorClass = '',
        array $cacheVariators = [],
        bool $isCacheable = true
    ) {
        parent::__construct(
            $cacheModel,
            $objectManager,
            $serializer,
            $arrayDiffProcessor,
            $processorClass,
            $cacheVariators,
            $isCacheable
        );

        $this->httpContext = $httpContext;
        $this->attributeValueUpdate = $attributeValueUpdate;
    }

    protected function processCache(array $diffData, array $jsLayout): array
    {
        $jsLayout = parent::processCache($diffData, $jsLayout);
        return $this->updateAttributesValues($jsLayout);
    }

    /**
     * Update dynamical default value of attributes
     * @param array $jsLayout
     *
     * @return array
     */
    private function updateAttributesValues(array $jsLayout): array
    {
        if ($this->isLoggedIn()) {
            $this->updateDefaultValueOfAddresses($jsLayout['components']['checkout']['children']['steps']['children']);
        }

        return $jsLayout;
    }

    /**
     * @param array $layoutChildren
     */
    private function updateDefaultValueOfAddresses(array &$layoutChildren): void
    {
        foreach ($layoutChildren as $key => &$items) {
            if (\is_array($items) && isset($items['children'])) {
                if (\in_array($key, static::ADRESS_FORM_NAMES, true)) {
                    $items['children'] = $this->attributeValueUpdate->updateDefaultValuesOfLayoutJs($items['children']);
                    continue;
                }

                $this->updateDefaultValueOfAddresses($items['children']);
            }
        }
    }

    /**
     * Is customer logged in
     *
     * @return bool
     */
    private function isLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(Context::CONTEXT_AUTH);
    }
}
