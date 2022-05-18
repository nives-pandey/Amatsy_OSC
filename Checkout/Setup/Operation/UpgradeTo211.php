<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface;
use Amasty\Checkout\Api\Data\OrderCustomFieldsInterface;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields;
use Amasty\Checkout\Model\ResourceModel\OrderCustomFields;

/**
 * Class UpgradeTo211
 */
class UpgradeTo211
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createQuoteCustomFieldsTable($setup);
        $this->createOrderCustomFieldsTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createQuoteCustomFieldsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(QuoteCustomFields::MAIN_TABLE)
        )->addColumn(
            QuoteCustomFieldsInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Field Id'
        )->addColumn(
            QuoteCustomFieldsInterface::NAME,
            Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            QuoteCustomFieldsInterface::BILLING_VALUE,
            Table::TYPE_TEXT,
            255,
            [],
            'Billing Value'
        )->addColumn(
            QuoteCustomFieldsInterface::SHIPPING_VALUE,
            Table::TYPE_TEXT,
            255,
            [],
            'Shipping Value'
        )->addColumn(
            QuoteCustomFieldsInterface::QUOTE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quote Id'
        )->addForeignKey(
            $setup->getFkName(
                QuoteCustomFields::MAIN_TABLE,
                QuoteCustomFieldsInterface::QUOTE_ID,
                'quote',
                'entity_id'
            ),
            QuoteCustomFieldsInterface::QUOTE_ID,
            $setup->getTable('quote'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Quote Custom Checkout Fields Values'
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createOrderCustomFieldsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(OrderCustomFields::MAIN_TABLE)
        )->addColumn(
            OrderCustomFieldsInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Field Id'
        )->addColumn(
            OrderCustomFieldsInterface::NAME,
            Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            OrderCustomFieldsInterface::BILLING_VALUE,
            Table::TYPE_TEXT,
            255,
            [],
            'Billing Value'
        )->addColumn(
            OrderCustomFieldsInterface::SHIPPING_VALUE,
            Table::TYPE_TEXT,
            255,
            [],
            'Shipping Value'
        )->addColumn(
            OrderCustomFieldsInterface::ORDER_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order Id'
        )->addForeignKey(
            $setup->getFkName(
                OrderCustomFields::MAIN_TABLE,
                OrderCustomFieldsInterface::ORDER_ID,
                'sales_order',
                'entity_id'
            ),
            OrderCustomFieldsInterface::ORDER_ID,
            $setup->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Order Custom Checkout Fields Values'
        );

        $setup->getConnection()->createTable($table);
    }
}
