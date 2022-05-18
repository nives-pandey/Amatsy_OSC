<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Amasty\Checkout\Api\Data\QuotePasswordsInterface;
use Amasty\Checkout\Model\ResourceModel\QuotePasswords;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Amasty\Checkout\Model\ResourceModel\Field\Store\CollectionFactory;
use Amasty\Checkout\Model\FieldFactory;
use Amasty\Checkout\Model\ResourceModel\Field;
use Amasty\Checkout\Model\ResourceModel\Field\Store;

/**
 * Class UpgradeTo230
 */
class UpgradeTo230
{
    /**
     * @var CollectionFactory
     */
    private $storeCollectionFactory;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var Field
     */
    private $fieldResource;

    public function __construct(
        CollectionFactory $storeCollectionFactory,
        FieldFactory $fieldFactory,
        Field $fieldResource
    ) {
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->fieldFactory = $fieldFactory;
        $this->fieldResource = $fieldResource;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createQuotePasswordsTable($setup);
        $this->addStoreIdColumn($setup);
        $this->transferLabelsByStoreIds($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createQuotePasswordsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(QuotePasswords::MAIN_TABLE)
        )->addColumn(
            QuotePasswordsInterface::ENTITY_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            QuotePasswordsInterface::QUOTE_ID,
            Table::TYPE_INTEGER,
            null,
            ['primary' => true, 'unsigned' => true, 'nullable' => false],
            'Quote Id'
        )->addColumn(
            QuotePasswordsInterface::PASSWORD_HASH,
            Table::TYPE_TEXT,
            128,
            [],
            'Password Hash'
        )->addForeignKey(
            $setup->getFkName(
                QuotePasswords::MAIN_TABLE,
                QuotePasswordsInterface::QUOTE_ID,
                'quote',
                'entity_id'
            ),
            QuotePasswordsInterface::QUOTE_ID,
            $setup->getTable('quote'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Storage for customer quote passwords (needed to create account while place order)'
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addStoreIdColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(Field::MAIN_TABLE);
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'store_id',
            [
                'type'     => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Store Id'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function transferLabelsByStoreIds(SchemaSetupInterface $setup)
    {
        /** @var \Amasty\Checkout\Model\ResourceModel\Field\Store\Collection $fieldCollection */
        $storeCollection = $this->storeCollectionFactory->create();

        /** @var \Amasty\Checkout\Model\Field\Store $storeField */
        foreach ($storeCollection->getItems() as $storeField) {
            /** @var \Amasty\Checkout\Model\Field $fieldModel */
            $fieldModel = $this->fieldFactory->create();
            $this->fieldResource->load($fieldModel, $storeField->getFieldId());

            $data = [
                'attribute_id' => $fieldModel->getAttributeId(),
                'label' => $storeField->getLabel(),
                'sort_order' => $fieldModel->getSortOrder(),
                'required' => $fieldModel->getRequired(),
                'width' => $fieldModel->getWidth(),
                'enabled' => $fieldModel->getEnabled(),
                'store_id' => $storeField->getStoreId()
            ];

            $fieldModel->unsetData('id');
            $fieldModel->addData($data);

            $this->fieldResource->save($fieldModel);
        }

        $setup->getConnection()->dropTable(
            $setup->getTable(Store::MAIN_TABLE)
        );
    }
}
