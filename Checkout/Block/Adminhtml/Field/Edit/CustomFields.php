<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Field\Edit;

use Magento\Customer\Model\Indexer\Address\AttributeProvider;
use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\Checkout\Model\Field;
use Amasty\Checkout\Model\ModuleEnable;
use Magento\Eav\Setup\EavSetup;
use Magento\Backend\Block\Template\Context;
use Amasty\Checkout\Helper\Onepage;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Amasty\Checkout\Block\Adminhtml\Renderer\Template;

/**
 * Class CustomFields
 */
class CustomFields extends Template
{
    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * CollectionFactory
     */
    private $eavCollectionFactory;

    public function __construct(
        Context $context,
        Onepage $helper,
        ModuleEnable $moduleEnable,
        EavSetup $eavSetup,
        CollectionFactory $eavCollectionFactory,
        array $data = []
    ) {
        $this->moduleEnable = $moduleEnable;
        $this->eavSetup = $eavSetup;
        $this->eavCollectionFactory = $eavCollectionFactory;

        parent::__construct($context, $helper, $data);
    }

    /**
     * @param int $index
     *
     * @return bool
     */
    public function isExistField($index)
    {
        return (bool)$this->eavSetup->getAttribute(AttributeProvider::ENTITY, 'custom_field_' . $index);
    }

    /**
     * @return bool
     */
    public function isExistOrderAttributesExt()
    {
        return $this->moduleEnable->isOrderAttributesEnable();
    }

    /**
     * @return bool
     */
    public function isAllCustomFieldsAdded()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavCollection */
        $eavCollection =  $this->eavCollectionFactory->create();
        $eavCollection->addFieldToFilter(
            [
                AttributeMetadataInterface::ATTRIBUTE_CODE,
                AttributeMetadataInterface::ATTRIBUTE_CODE,
                AttributeMetadataInterface::ATTRIBUTE_CODE
            ],
            [
                ['eq' => CustomFieldsConfigInterface::CUSTOM_FIELD_1_CODE],
                ['eq' => CustomFieldsConfigInterface::CUSTOM_FIELD_2_CODE],
                ['eq' => CustomFieldsConfigInterface::CUSTOM_FIELD_3_CODE]
            ]
        );

        if ($eavCollection->getSize() == CustomFieldsConfigInterface::COUNT_OF_CUSTOM_FIELDS) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_request->getParam(ScopeInterface::SCOPE_STORE, Field::DEFAULT_STORE_ID);
    }
}
