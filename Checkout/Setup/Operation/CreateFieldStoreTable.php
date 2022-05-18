<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Checkout\Model\ResourceModel\Field\Store;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Amasty\Checkout\Model\ResourceModel\Field;

/**
 * Class CreateFieldStoreTable
 */
class CreateFieldStoreTable
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
        $table = $setup->getTable(Store::MAIN_TABLE);

        return $table = $setup->getConnection()
            ->newTable($table)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'field_id',
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false],
                'Amasty Checkout Field ID'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'unsigned' => true],
                'Sort Order'
            )
            ->addColumn(
                'label',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Label'
            )
            ->addIndex(
                $setup->getIdxName(
                    Store::MAIN_TABLE,
                    ['field_id', 'store_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['field_id', 'store_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    Store::MAIN_TABLE,
                    'field_id',
                    Field::MAIN_TABLE,
                    'id'
                ),
                'field_id',
                $setup->getTable(Field::MAIN_TABLE),
                'id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    Store::MAIN_TABLE,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Amasty Checkout Field Store Table');
    }
}
