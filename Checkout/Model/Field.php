<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Amasty\Checkout\Model\ResourceModel\Field as FieldResource;
use Amasty\Checkout\Model\ResourceModel\Field\Collection;
use Amasty\Checkout\Model\ResourceModel\Field\CollectionFactory;
use Amasty\Checkout\Model\Config;

/**
 * Class Field
 */
class Field extends AbstractModel
{
    const XML_PATH_CONFIG = 'customer/address/';
    const MAGENTO_REQUIRE_CONFIG_VALUE = 'req';
    const DEFAULT_STORE_ID = 0;

    /**
     * @var array
     */
    private $notChangeableFields = [
        'postcode',
        'region'
    ];

    /**
     * @var ResourceModel\Field\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Registry $registry,
        FieldResource $resource,
        Collection $resourceCollection,
        CollectionFactory $attributeCollectionFactory,
        Config $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->configProvider = $configProvider;
    }

    protected function _construct()
    {
        $this->_init(FieldResource::class);
    }

    public function getInheritedAttributes()
    {
        return [
            'region_id' => 'region',
            'vat_is_valid' => 'vat_id',
            'vat_request_id' => 'vat_id',
            'vat_request_date' => 'vat_id',
            'vat_request_success' => 'vat_id',
        ];
    }

    /**
     * @param int $storeId
     *
     * @return \Amasty\Checkout\Model\Field[]
     */
    public function getConfig($storeId)
    {
        /** @var Collection $attributeCollection */
        $attributeCollection = $this->getAttributeCollectionByStoreId($storeId);

        $result = [];

        /** @var \Amasty\Checkout\Model\Field $item */
        foreach ($attributeCollection->getItems() as $item) {
            $item->setFieldDepend('checkout');
            $result[$item->getData('attribute_code')] = $item;

            if ($storeId != self::DEFAULT_STORE_ID && $item->getStoreId() == self::DEFAULT_STORE_ID) {
                $result[$item->getData('attribute_code')]['use_default'] = 1;
            }
        }

        return $result;
    }

    /**
     * The method gets tooltip
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTooltipInfo()
    {
        $fieldCode = $this->getData('attribute_code');
        $tooltip = __('To configure which customer attributes will be required to checkout please check settings at Stores > Configuration > Customers > Customer Configuration > Name and Address Options');
        if ($fieldCode == 'postcode') {
            $tooltip = __('To configure Postcode requirement for certain countries please check settings at Stores > Configuration > General > General > Country Options');
        } elseif ($fieldCode == 'region') {
            $tooltip = __('To configure State requirement for certain countries please check settings at Stores > Configuration > General > General > State Options');
        }

        return $tooltip;
    }

    /**
     * The method checks field is require or not
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->isMagentoRequired() || $this->getData('required');
    }

    /**
     * The method checks field is changeable
     *
     * @return bool
     */
    public function isNotChangeable()
    {
        $fieldCode = $this->getData('attribute_code');

        return in_array($fieldCode, $this->notChangeableFields) ?: $this->isMagentoRequired();
    }

    /**
     * The method checks field to have required store config
     *
     * @return bool
     */
    private function isMagentoRequired()
    {
        $mageConfigValue = $this->getMagentoConfigValue();

        return (bool)($mageConfigValue == self::MAGENTO_REQUIRE_CONFIG_VALUE);
    }

    /**
     * The method gets store config value
     *
     * @return mixed
     */
    private function getMagentoConfigValue()
    {
        $configKey = $this->getData('attribute_code') == 'vat_id' ? 'taxvat' : $this->getData('attribute_code');

        return $this->configProvider->getMagentoConfigValue(self::XML_PATH_CONFIG . $configKey . '_show');
    }

    /**
     * @param int $storeId
     *
     * @return ResourceModel\Field\Collection
     */
    public function getAttributeCollectionByStoreId($storeId = self::DEFAULT_STORE_ID)
    {
        return  $this->attributeCollectionFactory->create()->getAttributeCollectionByStoreId($storeId);
    }
}
