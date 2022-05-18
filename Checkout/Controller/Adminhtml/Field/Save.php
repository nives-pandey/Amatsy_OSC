<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Controller\Adminhtml\Field;

use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Amasty\Checkout\Controller\Adminhtml\Field as FieldAction;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\Field;
use Amasty\Checkout\Model\ModuleEnable;
use Amasty\Checkout\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use Amasty\Checkout\Model\FieldFactory;
use Amasty\Checkout\Model\ResourceModel\Field as FieldResource;
use Amasty\CustomerAttributes\Helper\Collection as CustomerAttributesHelper;
use Amasty\Orderattr\Model\ResourceModel\Attribute\CollectionFactory as OrderattrCollectionFactory;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Collection;
use Amasty\Orderattr\Model\Attribute\Attribute as OrderattrAttribute;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Customer\Model\AttributeFactory;
use Magento\Customer\Model\Indexer\Address\AttributeProvider;
use Magento\Customer\Model\ResourceModel\Attribute\Collection as CustomerAttributeCollection;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Customer\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\Eav\Model\Entity\Attribute\FrontendLabelFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttributeResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Save for save checkout fields
 */
class Save extends FieldAction
{
    /**
     * @var FieldResource
     */
    protected $fieldResource;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var FieldCollectionFactory
     */
    private $fieldCollectionFactory;

    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    /**
     * AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var AttributeResource
     */
    private $attributeResource;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var FrontendLabelFactory
     */
    private $frontendLabelFactory;

    /**
     * @var EavAttributeResource
     */
    private $eavAttributeResource;

    /**
     * @var EavCollectionFactory
     */
    private $eavCollectionFactory;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * Cache attributes ID of custom fields
     *
     * @var array|null
     */
    private $cacheEavCustomAttributes = null;

    public function __construct(
        Context $context,
        FieldResource $fieldResource,
        FieldFactory $fieldFactory,
        FieldCollectionFactory $fieldCollectionFactory,
        ModuleEnable $moduleEnable,
        AttributeCollectionFactory $attributeCollectionFactory,
        AttributeResource $attributeResource,
        AttributeFactory $attributeFactory,
        FrontendLabelFactory $frontendLabelFactory,
        EavAttributeResource $eavAttributeResource,
        EavCollectionFactory $eavCollectionFactory,
        EavSetup $eavSetup,
        Config $configProvider
    ) {
        parent::__construct($context);
        $this->fieldResource = $fieldResource;
        $this->fieldFactory = $fieldFactory;
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->moduleEnable = $moduleEnable;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeResource = $attributeResource;
        $this->attributeFactory = $attributeFactory;
        $this->frontendLabelFactory = $frontendLabelFactory;
        $this->eavAttributeResource = $eavAttributeResource;
        $this->eavCollectionFactory = $eavCollectionFactory;
        $this->eavSetup = $eavSetup;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        $fields = $this->_request->getParam('field');

        if (!is_array($fields)) {
            return $this->_redirect('*/*', ['_current' => true]);
        }

        try {
            $this->fieldResource->beginTransaction();

            $storeId = $this->_request->getParam(ScopeInterface::SCOPE_STORE, Field::DEFAULT_STORE_ID);

            if ($this->moduleEnable->isOrderAttributesEnable()) {
                $fields = $this->processOrderAttrFields($fields, $storeId);
            }

            if ($this->moduleEnable->isCustomerAttributesEnable()) {
                $fields = $this->processCustomerAttrFields($fields, $storeId);
            }

            $storeAttributeIds = [];

            /** @var \Amasty\Checkout\Model\ResourceModel\Field\Collection $fieldCollection */
            $fieldCollection = $this->fieldCollectionFactory->create();
            $fieldCollection->addFilterByStoreId($storeId);

            if ($storeId != Field::DEFAULT_STORE_ID) {
                $storeAttributeIds = $this->getStoreAttributeIds($fieldCollection, $fields);
            }

            $this->saveDefaultFields($fields, $storeId, $storeAttributeIds);

            $this->fieldResource->commit();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->fieldResource->rollBack();

            return $this->_redirect('*/*', ['_current' => true]);
        }

        $this->messageManager->addSuccessMessage(__('Fields information has been successfully updated'));

        return $this->_redirect('*/*', ['_current' => true]);
    }

    /**
     * @param \Amasty\Checkout\Model\ResourceModel\Field\Collection $fieldCollection
     * @param array $fields
     *
     * @return array
     */
    private function getStoreAttributeIds($fieldCollection, $fields)
    {
        $attributeIds = [];

        /** @var Field $field */
        foreach ($fieldCollection->getItems() as $field) {
            if (isset($fields[$field->getAttributeId()])) {
                if (!isset($fields[$field->getAttributeId()]['use_default'])) {
                    $attributeId = $field->getAttributeId();
                    $attributeIds[] = $attributeId;

                    if (!isset($fields[$attributeId]['attribute_id'])) {
                        $fields[$attributeId]['attribute_id'] = $attributeId;
                    }

                    $this->saveField($field, $fields[$attributeId]);
                } else {
                    $this->fieldResource->deleteField($field);
                }
            }
        }

        return $attributeIds;
    }

    /**
     * @param array $fields
     * @param int $storeId
     * @param array $storeAttributeIds
     */
    private function saveDefaultFields($fields, $storeId, $storeAttributeIds)
    {
        foreach ($fields as $attributeId => $fieldData) {
            /** @var Field $field */
            $field = $this->fieldFactory->create();

            if ($storeId != Field::DEFAULT_STORE_ID) {
                if (in_array($attributeId, $storeAttributeIds) || !empty($fieldData['use_default'])) {
                    continue;
                }
            } else {
                $fieldId = $fieldData['id'];
                $this->fieldResource->load($field, $fieldId);
            }

            if (!isset($fieldData['required'])) {
                $fieldData['required'] = 0;
            } else {
                $fieldData['required'] = 1;
            }

            if (!isset($fieldData['store_id'])) {
                $fieldData['store_id'] = $storeId;
            }

            if (!isset($fieldData['attribute_id'])) {
                $fieldData['attribute_id'] = $attributeId;
            }

            if (!isset($fieldData['enabled']) || $fieldData['enabled'] == 0) {
                $this->changeFieldGlobalSettings($attributeId);
            }

            $this->saveField($field, $fieldData);
        }
    }

    /**
     * Create cache from attributes ID of custom fields
     *
     * @return void
     */
    private function createCacheEavCustomAttributes()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavCollection */
        $eavCollection =  $this->eavCollectionFactory->create();
        $field = [];
        $condition = [];
        $countCustomFields = CustomFieldsConfigInterface::COUNT_OF_CUSTOM_FIELDS;

        for ($i = 1; $i <= $countCustomFields; $i++) {
            $constNameCustomField
                = '\Amasty\Checkout\Api\Data\CustomFieldsConfigInterface::CUSTOM_FIELD_' . $i . '_CODE';

            if (!defined($constNameCustomField)) {
                continue;
            }

            $field[] = AttributeMetadataInterface::ATTRIBUTE_CODE;
            $condition[] = ['eq' => constant($constNameCustomField)];
        }

        $eavCollection->addFieldToFilter($field, $condition);
        $this->cacheEavCustomAttributes = [];

        foreach ($eavCollection as $item) {
            $this->cacheEavCustomAttributes[] = $item->getAttributeId();
        }
    }

    /**
     * Check attribute is custom field by ID
     *
     * @param int|string|null $attributeId
     * @return bool
     */
    private function isCustomFieldAttribute($attributeId = null)
    {
        if (empty($attributeId)) {
            return false;
        }

        if ($this->cacheEavCustomAttributes === null) {
            $this->createCacheEavCustomAttributes();
        }

        return in_array($attributeId, $this->cacheEavCustomAttributes);
    }

    /**
     * @param Field $field
     * @param array $fieldData
     */
    private function saveField($field, $fieldData)
    {
        $field->addData(array_intersect_key($fieldData, array_flip([
            'attribute_id', 'sort_order', 'enabled', 'width', 'required', 'label', 'store_id'
        ])));

        if ($this->isCustomFieldAttribute($fieldData['attribute_id'])) {
            $attribute = $this->eavSetup->getAttribute(AttributeProvider::ENTITY, $fieldData['attribute_id']);

            if ($attribute) {
                $this->eavSetup->updateAttribute(
                    $attribute['entity_type_id'],
                    $attribute['attribute_id'],
                    'frontend_label',
                    $fieldData['label']
                );
            }
        }

        $this->fieldResource->save($field);
    }

    /**
     * @param array $fields
     * @param int $storeId
     *
     * @return array
     */
    private function processOrderAttrFields($fields, $storeId)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();
        /** @var OrderattrCollectionFactory $orderAttrCollectionFactory */
        $orderAttrCollectionFactory = $objectManager->create(OrderattrCollectionFactory::class);
        /** @var Collection $orderAttrCollection */
        $orderAttrCollection = $orderAttrCollectionFactory->create();

        if ($storeId != Field::DEFAULT_STORE_ID) {
            $orderAttrCollection->addStoreFilter($storeId);
        }

        /** @var OrderattrAttribute $attribute */
        foreach ($orderAttrCollection->getItems() as $attribute) {
            if (isset($fields[$attribute->getId()])) {
                if (empty($fields[$attribute->getId()]['use_default'])) {
                    $attribute = $this->prepareAttribute($attribute, $fields, $attribute->getId(), $storeId);
                    $attribute->setIsVisibleOnFront($fields[$attribute->getId()]['enabled']);
                    $attribute->setValidateRules([]);

                    $this->eavAttributeResource->save($attribute);
                }
            }

            unset($fields[$attribute->getId()]);
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param int $storeId
     *
     * @return array
     */
    private function processCustomerAttrFields($fields, $storeId)
    {
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

        foreach ($attrCollection->getAllIds() as $attributeId) {
            if (isset($fields[$attributeId])) {
                if (empty($fields[$attributeId]['use_default'])) {
                    /** @var CustomerAttribute $attribute */
                    $attribute = $this->attributeFactory->create();
                    $this->attributeResource->load($attribute, $attributeId);

                    $attribute = $this->prepareAttribute($attribute, $fields, $attributeId, $storeId);
                    $attribute->setSortOrder($fields[$attributeId]['sort_order'] + 1000);
                    $attribute->setUsedInProductListing($fields[$attributeId]['enabled']);

                    $usedInForms = $this->getUsedForms($attribute, $fields, $attributeId);

                    $attribute->setUsedInForms($usedInForms);

                    $this->attributeResource->save($attribute);
                }
            }

            unset($fields[$attributeId]);
        }

        return $fields;
    }

    /**
     * @param OrderattrAttribute|CustomerAttribute $attribute
     * @param array $fields
     * @param int $attributeId
     * @param int $storeId
     *
     * @return OrderattrAttribute|CustomerAttribute
     * @throws NoSuchEntityException
     */
    private function prepareAttribute($attribute, $fields, $attributeId, $storeId)
    {
        if ($storeId != Field::DEFAULT_STORE_ID) {
            $this->saveStorelabel($attribute, $storeId, $fields[$attributeId]['label']);
        } else {
            if (!$fields[$attributeId]['label']) {
                throw new NoSuchEntityException(__("'frontend_label' is required. Enter and try again."));
            } else {
                $attribute->setFrontendLabel($fields[$attributeId]['label']);
            }

            if (isset($fields[$attributeId]['required'])) {
                $attribute->setIsRequired(1);
            } else {
                $attribute->setIsRequired(0);
            }
        }

        $attribute->setSortingOrder($fields[$attributeId]['sort_order']);

        return $attribute;
    }

    /**
     * @param OrderattrAttribute|CustomerAttribute $attribute
     * @param int $storeId
     * @param string $storeLabel
     */
    private function saveStorelabel($attribute, $storeId, $storeLabel)
    {
        if ($attribute->getFrontendLabels()) {
            foreach ($attribute->getFrontendLabels() as $labelData) {
                if ($labelData->getStoreId() == $storeId) {
                    $labelData->setLabel($storeLabel);
                } else {
                    $this->createFrontendLabel($attribute, $storeId, $storeLabel);
                }
            }
        } else {
            $this->createFrontendLabel($attribute, $storeId, $storeLabel);
        }
    }

    /**
     * @param OrderattrAttribute|CustomerAttribute $attribute
     * @param int $storeId
     * @param string $storeLabel
     */
    private function createFrontendLabel($attribute, $storeId, $storeLabel)
    {
        /** @var FrontendLabel $frontendLabel */
        $frontendLabel = $this->frontendLabelFactory->create();
        $frontendLabel->setData(['store_id' => $storeId, 'label' => $storeLabel]);

        if ($attribute->getFrontendLabels()) {
            $frontendLabels = array_merge($attribute->getFrontendLabels(), [$frontendLabel]);
        } else {
            $frontendLabels = [$frontendLabel];
        }

        $storeLabels = $attribute->getStoreLabels();
        $storeLabels[$storeId] = $storeLabel;

        $attribute->setStoreLabels($storeLabels);
        $attribute->setFrontendLabels($frontendLabels);
    }

    /**
     * @param CustomerAttribute $attribute
     * @param array $fields
     * @param int $attributeId
     *
     * @return array
     */
    private function getUsedForms($attribute, $fields, $attributeId)
    {
        $usedInForms = [
            'adminhtml_customer',
            'amasty_custom_attribute'
        ];

        if ($attribute->getIsVisibleOnFront()) {
            $usedInForms[] = 'customer_account_edit';
        }

        if ($attribute->getOnRegistration()) {
            $usedInForms[] = 'customer_account_create';
            $usedInForms[] = 'customer_attributes_registration';
        }

        if ($fields[$attributeId]['enabled']) {
            $usedInForms[] = 'adminhtml_checkout';
            $usedInForms[] = 'customer_attributes_checkout';
        }

        return $usedInForms;
    }

    /**
     * Need to change global setting(required to fill) for validation on checkout
     *
     * @param int $attributeId
     * @throws AlreadyExistsException
     */
    private function changeFieldGlobalSettings($attributeId)
    {
        /** @var CustomerAttribute $eavField */
        $eavField = $this->attributeFactory->create();
        $this->eavAttributeResource->load($eavField, $attributeId);

        if ($eavField->getAttributeCode() === AddressInterface::TELEPHONE) {
            $this->configProvider->saveTelephoneOption('');
            $eavField->setIsRequired(false);
            $this->eavAttributeResource->save($eavField);
        }
    }
}
