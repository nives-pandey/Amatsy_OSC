<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Checkout\Model\Field;

/**
 * Class InstallData
 *
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer address
     *
     * @var Address
     */
    protected $customerAddress;

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var Field
     */
    protected $fieldSingleton;

    public function __construct(
        Address $customerAddress,
        CollectionFactory $attributeCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        Field $fieldSingleton
    ) {
        $this->customerAddress = $customerAddress;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->fieldSingleton = $fieldSingleton;
    }

    protected function isSettingEnabled($setting)
    {
        $connection = $this->setup->getConnection();

        $select = $connection->select()->from(
            $this->setup->getTable('core_config_data'),
            'COUNT(*)'
        )->where(
            'path=?',
            $setting
        )->where(
            'value NOT LIKE ?',
            '0'
        );

        return $connection->fetchOne($select) > 0;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        /** @var \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection $attributes */
        $attributes = $this->attributeCollectionFactory->create();

        $connection = $setup->getConnection();

        $inheritedAttributes = $this->fieldSingleton->getInheritedAttributes();

        /** @var \Magento\Customer\Model\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();

            if (isset($inheritedAttributes[$code])) {
                continue;
            }

            if ($code == 'vat_id') {
                $code = 'taxvat';
            }

            if ($code == 'fax') {
                $isEnabled = false;
            } else {
                if (in_array($code, ['prefix', 'suffix', 'middlename', 'taxvat'])) {
                    $isEnabled = (bool)$this->customerAddress->getConfig($code)
                        || $this->isSettingEnabled('customer/address/' . $code . '_show');
                } else {
                    $isEnabled = true;
                }
            }

            $bind = [
                'attribute_id' => $attribute->getId(),
                'label'        => $attribute->getDefaultFrontendLabel(),
                'sort_order'   => $attribute->getSortOrder(),
                'required'     => $attribute->getIsRequired(),
                'width'        => 100,
                'enabled'      => $isEnabled
            ];

            $connection->insert($setup->getTable('amasty_amcheckout_field'), $bind);
        }

        $setup->endSetup();
    }
}
