<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Checkout\Model\ResourceModel\Field;

/**
 * Class CreateFieldTable
 */
class CreateFieldTable
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
        $table = $setup->getTable(Field::MAIN_TABLE);

        return $table = $setup->getConnection()
            ->newTable($table)
            ->addColumn(
                'id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false],
                'EAV Attribute ID'
            )
            ->addColumn(
                'label',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Label'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Sort Order'
            )
            ->addColumn(
                'required',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Required'
            )
            ->addColumn(
                'width',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Width'
            )
            ->addColumn(
                'enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Enabled'
            )
            ->addForeignKey(
                $setup->getFkName(
                    Field::MAIN_TABLE,
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Amasty Checkout Field Table');
    }
}
