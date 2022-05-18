<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Observer\Order;

use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Amasty\Checkout\Model\ResourceModel\OrderCustomFields\CollectionFactory;
use Amasty\Checkout\Api\Data\OrderCustomFieldsInterface;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\ResourceModel\Field\CollectionFactory as AttributeCollectionFactory;

/**
 * Class RendererAddressFormat
 */
class RendererAddressFormat implements ObserverInterface
{
    /**
     * Custom address format
     */
    const CUSTOM_FIELD_1_VAR = "{{depend custom_field_1}}<br />Custom Field 1: {{var custom_field_1}}{{/depend}}";
    const CUSTOM_FIELD_2_VAR = "{{depend custom_field_2}}<br />Custom Field 2: {{var custom_field_2}}{{/depend}}";
    const CUSTOM_FIELD_3_VAR = "{{depend custom_field_3}}<br />Custom Field 3: {{var custom_field_3}}{{/depend}}";

    /**
     * @var CollectionFactory
     */
    private $orderCustomFieldsCollection;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    public function __construct(
        CollectionFactory $orderCustomFieldsCollection,
        Config $configProvider,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->orderCustomFieldsCollection = $orderCustomFieldsCollection;
        $this->configProvider = $configProvider;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $observer->getAddress();

        if (!$address->getOrder()) {
            return;
        }

        $addressType = $address->getAddressType();

        if (\Amasty\Checkout\Model\CustomFormatFlag::$flag) {
            $customField1Var = self::CUSTOM_FIELD_1_VAR;
            $customField2Var = self::CUSTOM_FIELD_2_VAR;
            $customField3Var = self::CUSTOM_FIELD_3_VAR;

            $countOfCustomFields = CustomFieldsConfigInterface::COUNT_OF_CUSTOM_FIELDS;
            $index = CustomFieldsConfigInterface::CUSTOM_FIELD_INDEX;

            for ($index; $index <= $countOfCustomFields; $index++) {
                $attrCustomFieldId = $this->configProvider->getAttributeId(
                    'customer_address',
                    'custom_field_' . $index
                );

                if ($attrCustomFieldId != null) {
                    /** @var \Amasty\Checkout\Model\ResourceModel\Field\Collection $attributeCollection */
                    $attributeCollection = $this->attributeCollectionFactory->create();
                    $attributeCollection
                        ->addFieldToSelect('label')
                        ->addFieldToFilter('attribute_id', $attrCustomFieldId);

                    if ($attributeCollection->getSize()) {
                        $items = $attributeCollection->getItems();
                        $label = $items[0]->getLabel();
                        ${'customField' . $index . 'Var'} =
                            "{{depend custom_field_$index}}<br /> $label: {{var custom_field_$index}}{{/depend}}";
                    }
                }
            }

            /** @var \Magento\Framework\DataObject $formatType */
            $formatType = $observer->getType();
            $formatType->setDefaultFormat(
                $formatType->getDefaultFormat()
                . $customField1Var
                . $customField2Var
                . $customField3Var
            );

            \Amasty\Checkout\Model\CustomFormatFlag::$flag = false;
        }

        /** @var \Amasty\Checkout\Model\ResourceModel\OrderCustomFields\Collection $orderCustomFieldsCollection */
        $orderCustomFieldsCollection = $this->orderCustomFieldsCollection->create();
        $orderCustomFieldsCollection->addFieldByOrderId($address->getOrder()->getId());

        $customFieldsData = $this->prepareCustomFieldData($orderCustomFieldsCollection, $addressType);

        $address->addData($customFieldsData);
    }

    /**
     * @param \Amasty\Checkout\Model\ResourceModel\OrderCustomFields\Collection $orderCustomFieldsCollection
     * @param string $addressType
     *
     * @return array
     */
    private function prepareCustomFieldData($orderCustomFieldsCollection, $addressType)
    {
        $customFieldsData = [];

        foreach ($orderCustomFieldsCollection->getItems() as $orderCustomField) {
            $orderCustomField = $orderCustomField->getData();
            $customFieldsData[$orderCustomField[OrderCustomFieldsInterface::NAME]]
                = $orderCustomField[$addressType . '_value'];
        }

        return $customFieldsData;
    }
}
