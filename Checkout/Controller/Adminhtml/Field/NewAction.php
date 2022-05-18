<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Controller\Adminhtml\Field;

use Amasty\Checkout\Controller\Adminhtml\Field as FieldAction;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Indexer\Address\AttributeProvider;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\Checkout\Model\Field;
use Amasty\Checkout\Model\ResourceModel\Field as FieldResource;
use Amasty\Checkout\Model\FieldFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

class NewAction extends FieldAction
{
    /**
     * @var FieldResource
     */
    private $fieldResource;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        Context $context,
        FieldResource $fieldResource,
        FieldFactory $fieldFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        parent::__construct($context);
        $this->fieldResource = $fieldResource;
        $this->fieldFactory = $fieldFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $fields = $this->_request->getParams();
        $storeId = $this->_request->getParam(ScopeInterface::SCOPE_STORE, Field::DEFAULT_STORE_ID);

        if (!is_array($fields)) {
            return $this->_redirect('*/*', ['_current' => true]);
        }

        foreach ($fields as $fieldId => $fieldData) {
            if ($fieldId === 'key' || $fieldId === ScopeInterface::SCOPE_STORE) {
                continue;
            }

            /** @var Field $fieldModel */
            $fieldModel = $this->fieldFactory->create();

            $index = preg_replace("/[^0-9]/", '', $fieldId);

            /** @var EavSetup $attribute */
            $attribute = $this->createEavAttribute($index);
            $attributeId = $attribute->getAttributeId(AttributeProvider::ENTITY, 'custom_field_' . $index);

            $this->fieldResource->load($fieldModel, $attributeId, 'attribute_id');

            if ($storeId != \Amasty\Checkout\Model\Field::DEFAULT_STORE_ID && !$fieldModel->getData()) {
                $this->createField(Field::DEFAULT_STORE_ID, $attributeId, $index, $fieldModel);
            }

            $this->createField($storeId, $attributeId, $index, $fieldModel);
        }

        return $this->_redirect('*/*', ['_current' => true, '_query' => '']);
    }

    /**
     * @param int $index
     *
     * @return EavSetup
     */
    private function createEavAttribute($index)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        $attribute =  $eavSetup->addAttribute(
            AttributeProvider::ENTITY,
            'custom_field_' . $index,
            [
                'group' => 'General',
                'type' => 'static',
                'backend' => '',
                'frontend' => '',
                'label' => 'Custom Field ' . $index,
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => '',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        return $attribute;
    }

    /**
     * @param int $storeId
     * @param int $attributeId
     * @param int $index
     * @param Field $fieldModel
     */
    private function createField($storeId, $attributeId, $index, $fieldModel)
    {
        $fieldModel->unsetData('id');

        $data = [
            'attribute_id' => $attributeId,
            'label' => 'Custom Field ' . $index,
            'sort_order' => 100 + $index,
            'required' => 0,
            'width' => 100,
            'enabled' => 1,
            'store_id' => $storeId
        ];

        $fieldModel->addData($data);
        $this->fieldResource->save($fieldModel);
    }
}
