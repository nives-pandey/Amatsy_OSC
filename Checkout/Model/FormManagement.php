<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Block\Adminhtml\Field\Edit\GroupFactory;
use Amasty\Checkout\Model\Field;
use Amasty\Orderattr\Model\ResourceModel\Attribute\CollectionFactory as OrderattrCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\FormFactory;
use Amasty\Checkout\Model\UrlManagement;
use Amasty\Checkout\Model\FieldFactory;
use Amasty\Checkout\Model\ModuleEnable;
use Amasty\Checkout\Api\Data\ManageCheckoutTabsInterface;
use Amasty\Checkout\Block\Adminhtml\Field\Edit\Group;
use Magento\Framework\Data\Form;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Collection as OrderattrCollection;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Customer\Model\ResourceModel\Attribute\Collection as CustomerAttributeCollection;
use Amasty\CustomerAttributes\Helper\Collection as CustomerAttributesHelper;
use Amasty\Orderattr\Model\Attribute\Attribute as OrderAttribute;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\Exception\LocalizedException;

class FormManagement
{
    /**#@+*/
    const ORDER_ATTRIBUTES_DEPEND = 'order_attr';
    const CUSTOMER_ATTRIBUTES_DEPEND = 'customer_attr';
    const SHIPPING_STEP = 2;
    const PAYMENT_STEP = 3;
    const SHIPPING_METHODS = 4;
    const PAYMENT_PLACE_ORDER = 5;
    const ORDER_SUMMARY = 6;
    /**#@-*/

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Field
     */
    private $fieldSingleton;

    /**
     * @var UrlManagement
     */
    private $urlManagement;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    /**
     * AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var Group
     */
    private $groupRows;

    public function __construct(
        GroupFactory $groupFactory,
        FormFactory $formFactory,
        Field $fieldSingleton,
        UrlManagement $urlManagement,
        FieldFactory $fieldFactory,
        ModuleEnable $moduleEnable,
        AttributeCollectionFactory $attributeCollectionFactory,
        Group $groupRows
    ) {
        $this->groupFactory = $groupFactory;
        $this->formFactory = $formFactory;
        $this->fieldSingleton = $fieldSingleton;
        $this->urlManagement = $urlManagement;
        $this->fieldFactory = $fieldFactory;
        $this->moduleEnable = $moduleEnable;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->groupRows = $groupRows;
    }

    /**
     * @param $tabId
     * @param $storeId
     *
     * @return Form
     * @throws LocalizedException
     */
    public function prepareForm($tabId, $storeId)
    {
        /** @var Form $form */
        $form = $this->formFactory->create();

        $fields = [];

        switch ($tabId) {
            case ManageCheckoutTabsInterface::CUSTOMER_INFO_TAB:
                $form = $this->createCustomFieldsButton($form);
                $form = $this->createOrderFieldsButton($form, self::SHIPPING_STEP);
                $form = $this->createCustomerFieldsButton($form);
                $fields = array_merge(
                    $this->fieldSingleton->getConfig($storeId),
                    $this->getOrderAttributeFields($storeId, [self::SHIPPING_STEP]),
                    $this->getCustomerAttributeFields($storeId)
                );
                break;
            case ManageCheckoutTabsInterface::SHIPPING_METHOD_TAB:
                $form = $this->createOrderFieldsButton($form, self::SHIPPING_METHODS);
                $fields = $this->getOrderAttributeFields($storeId, [self::SHIPPING_METHODS]);
                break;
            case ManageCheckoutTabsInterface::PAYMENT_METHOD_TAB:
                $form = $this->createOrderFieldsButton($form, self::PAYMENT_STEP);
                $fields = $this->getOrderAttributeFields(
                    $storeId,
                    [self::PAYMENT_STEP, self::PAYMENT_PLACE_ORDER]
                );
                break;
            case ManageCheckoutTabsInterface::ORDER_SUMMARY_TAB:
                $form = $this->createOrderFieldsButton($form, self::ORDER_SUMMARY);
                $fields = $this->getOrderAttributeFields($storeId, [self::ORDER_SUMMARY]);
                break;
        }

        $visible = $this->addGroup(
            $form,
            'visible_fields',
            __('Enabled Checkout Fields'),
            1
        );

        $invisible = $this->addGroup(
            $form,
            'invisible_fields',
            __('Disabled Checkout Fields'),
            0
        );

        $this->sortFields($fields);

        /** @var Field $field */
        foreach ($fields as $field) {
            $targetGroup = $field->getData('enabled') ? $visible : $invisible;

            $targetGroup->addRow('field_' . $field->getData('attribute_id'), ['field' => $field]);
        }

        return $form;
    }

    /**
     * @param Form $form
     * @param string $groupId
     * @param string $title
     * @param bool $enabled
     *
     * @return Group
     */
    public function addGroup(Form $form, $groupId, $title, $enabled)
    {
        /** @var Group $group */
        $group = $this->groupFactory->create();
        $group->setId($groupId);
        $group->setRenderer($this->groupRows->getGroupRenderer());
        $group->setData('title', $title);
        $group->setData('enabled', $enabled);

        $form->addElement($group);

        return $group;
    }

    /**
     * @param int $storeId
     * @param array $checkoutSteps
     *
     * @return array
     */
    public function getOrderAttributeFields($storeId, $checkoutSteps)
    {
        $orderAttributes = [];

        if ($this->moduleEnable->isOrderAttributesEnable()) {
            /** @var ObjectManager $objectManager */
            $objectManager = ObjectManager::getInstance();
            /** @var OrderattrCollectionFactory $orderAttrCollectionFactory */
            $orderAttrCollectionFactory = $objectManager->create(OrderattrCollectionFactory::class);
            /** @var OrderattrCollection $orderAttrCollection */
            $orderAttrCollection = $orderAttrCollectionFactory->create();

            if ($checkoutSteps) {
                $orderAttrCollection->addFieldToFilter('checkout_step', ['in' => $checkoutSteps]);
            }

            if ($storeId != Field::DEFAULT_STORE_ID) {
                $orderAttrCollection->addStoreFilter($storeId);
            }

            if ($orderAttrCollection->getSize()) {
                $orderAttributes = $this->prepareAdditionalFields(
                    $orderAttrCollection->getItems(),
                    $storeId,
                    self::ORDER_ATTRIBUTES_DEPEND
                );
            }
        }

        return $orderAttributes;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    private function getCustomerAttributeFields($storeId)
    {
        $customerAttributes = [];

        if ($this->moduleEnable->isCustomerAttributesEnable()) {
            /** @var ObjectManager $objectManager */
            $objectManager = ObjectManager::getInstance();
            /** @var AttributeCollectionFactory $attrCollectionFactory */
            $customerAttributesHelper = $objectManager->create(CustomerAttributesHelper::class);
            /** @var CustomerAttributeCollection $attrCollection */
            $attrCollection = $this->attributeCollectionFactory->create()
                ->addVisibleFilter();

            if ($storeId != Field::DEFAULT_STORE_ID) {
                $attrCollection->addFieldToFilter(
                    'store_ids',
                    [
                        ['eq' => $storeId],
                        ['like' => $storeId . ',%'],
                        ['like' => '%,' . $storeId],
                        ['like' => '%,' . $storeId . ',%']
                    ]
                );
            }

            $attrCollection = $customerAttributesHelper->addFilters(
                $attrCollection,
                'eav_attribute',
                [
                    "is_user_defined = 1",
                    "attribute_code != 'customer_activated' "
                ]
            );

            if ($attrCollection->getSize()) {
                $customerAttributes = $this->prepareAdditionalFields(
                    $attrCollection->getItems(),
                    $storeId,
                    self::CUSTOMER_ATTRIBUTES_DEPEND
                );
            }
        }

        return $customerAttributes;
    }

    /**
     * @param array $attributes
     * @param int $storeId
     * @param string $moduleDepend
     *
     * @return array
     */
    private function prepareAdditionalFields($attributes, $storeId, $moduleDepend)
    {
        $additionalAttributes = [];

        /** @var OrderAttribute|EavAttribute $item */
        foreach ($attributes as $item) {
            /** @var Field $fieldModel */
            $fieldModel = $this->fieldFactory->create();

            $frontendLabel = $item->getFrontendLabel();

            if ($storeId != Field::DEFAULT_STORE_ID) {
                $this->prepareStorelabel($item, $storeId, $frontendLabel);
            }

            if ($moduleDepend === self::CUSTOMER_ATTRIBUTES_DEPEND) {
                $fieldModel->setData('enabled', $item->getUsedInProductListing());
            } else {
                $fieldModel->setData('enabled', $item->getIsVisibleOnFront());
            }

            $fieldModel->setData('attribute_id', $item->getAttributeId());
            $fieldModel->setData('attribute_code', $item->getAttributeCode());
            $fieldModel->setData('label', $frontendLabel);
            $fieldModel->setData('default_label', $item->getFrontendLabel());
            $fieldModel->setData('width', 0);
            $fieldModel->setData('required', $item->getIsRequired());
            $fieldModel->setData('store_id', $storeId);
            $fieldModel->setData('sort_order', $item->getSortingOrder());
            $fieldModel->setData('field_depend', $moduleDepend);

            $additionalAttributes[$item->getAttributeCode()] = $fieldModel;
        }

        return $additionalAttributes;
    }

    /**
     * @param Form $form
     * @param int $checkoutStepPosition
     *
     * @return Form
     */
    public function createOrderFieldsButton($form, $checkoutStepPosition)
    {
        $form->addField(
            'order-fields-button',
            'button',
            [
                'onclick' => sprintf(
                    "window.open('%s');",
                    $this->urlManagement->getUrl(
                        'amorderattr/attribute/create',
                        ['position' => $checkoutStepPosition]
                    )
                ),
                'value' => __('Add Order Attribute'),
                'class' => 'action-default scalable',
                'disabled' => !$this->moduleEnable->isOrderAttributesEnable(),
                'title' => __('Install Order Attributes for Magento 2 by Amasty to unlock')
            ]
        );

        return $form;
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    private function createCustomFieldsButton($form)
    {
        $form->addField(
            'custom-fields-button',
            'button',
            [
                'onclick' => 'jQuery(\'#custom-fields\').modal(\'openModal\')',
                'value' => __('Add Custom Fields'),
                'class' => 'action-default scalable'
            ]
        );

        return $form;
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    private function createCustomerFieldsButton($form)
    {
        $form->addField(
            'customer-fields-button',
            'button',
            [
                'onclick' => sprintf(
                    "window.open('%s');",
                    $this->urlManagement->getUrl('amcustomerattr/attribute/new')
                ),
                'value' => __('Add Customer Attribute'),
                'class' => 'action-default scalable',
                'disabled' => !$this->moduleEnable->isCustomerAttributesEnable(),
                'title' => __('Install Customer Attributes for Magento 2 by Amasty to unlock')
            ]
        );

        return $form;
    }

    /**
     * @param array $fields
     */
    public function sortFields(&$fields)
    {
        usort($fields, function ($firstField, $secondField) {
            return $firstField->getSortOrder() - $secondField->getSortOrder();
        });
    }

    /**
     * @param OrderAttribute|EavAttribute $attribute
     * @param int $storeId
     * @param string $frontendLabel
     */
    private function prepareStorelabel($attribute, $storeId, &$frontendLabel)
    {
        if ($attribute->getStoreLabels()) {
            foreach ($attribute->getStoreLabels() as $store => $label) {
                if ($store == $storeId) {
                    $frontendLabel = $label;
                }
            }
        }
    }
}
