<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Checkout\Setup\Operation\CreateAdditionalTable;
use Amasty\Checkout\Setup\Operation\CreateFieldTable;
use Amasty\Checkout\Setup\Operation\CreateFieldStoreTable;
use Amasty\Checkout\Setup\Operation\CreateAdditionalFeeTable;
use Amasty\Checkout\Setup\Operation\CreateDeliveryTable;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateAdditionalTable
     */
    private $createAdditionalTable;

    /**
     * @var CreateFieldTable
     */
    private $createFieldTable;

    /**
     * @var CreateFieldStoreTable
     */
    private $createFieldStoreTable;

    /**
     * @var CreateAdditionalFeeTable
     */
    private $createAdditionalFeeTable;

    /**
     * @var CreateDeliveryTable
     */
    private $createDeliveryTable;

    public function __construct(
        CreateAdditionalTable $createAdditionalTable,
        CreateFieldTable $createFieldTable,
        CreateFieldStoreTable $createFieldStoreTable,
        CreateAdditionalFeeTable $createAdditionalFeeTable,
        CreateDeliveryTable $createDeliveryTable
    ) {
        $this->createAdditionalTable = $createAdditionalTable;
        $this->createFieldTable = $createFieldTable;
        $this->createFieldStoreTable = $createFieldStoreTable;
        $this->createAdditionalFeeTable = $createAdditionalFeeTable;
        $this->createDeliveryTable = $createDeliveryTable;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createFieldTable->execute($installer);
        $this->createFieldStoreTable->execute($installer);
        $this->createAdditionalFeeTable->execute($installer);
        $this->createDeliveryTable->execute($installer);
        $this->createAdditionalTable->execute($installer);

        $installer->endSetup();
    }
}
