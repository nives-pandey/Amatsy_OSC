<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\CreateAdditionalTable
     */
    private $createAdditionalTable;

    /**
     * @var Operation\UpgradeTo211
     */
    private $upgradeTo211;

    /**
     * @var Operation\UpgradeTo230
     */
    private $upgradeTo230;

    public function __construct(
        Operation\CreateAdditionalTable $createAdditionalTable,
        Operation\UpgradeTo211 $upgradeTo211,
        Operation\UpgradeTo230 $upgradeTo230
    ) {
        $this->createAdditionalTable = $createAdditionalTable;
        $this->upgradeTo211 = $upgradeTo211;
        $this->upgradeTo230 = $upgradeTo230;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.6.0', '<')) {
            $this->addDeliveryDateCommentColumn($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->createAdditionalTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $this->upgradeTo211->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->upgradeTo230->execute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addDeliveryDateCommentColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amcheckout_delivery');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'comment',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Delivery Comment'
            ]
        );
    }
}
