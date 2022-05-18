<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Amasty\Checkout\Model\ResourceModel\AdditionalFields;

/**
 * Class CreateAdditionalTable
 */
class CreateAdditionalTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(AdditionalFields::MAIN_TABLE);

        return $table = $setup->getConnection()
            ->newTable(
                $table
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'quote_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Quote Id'
            )->addColumn(
                'comment',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true, 'default' => null],
                'Order Comment'
            )->addColumn(
                'is_subscribe',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Subscribe Customer'
            )->addColumn(
                'is_register',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Register Customer'
            )->addColumn(
                'register_dob',
                Table::TYPE_TEXT,
                255,
                [],
                'Date of Birth'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    ['quote_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['quote_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
    }
}
