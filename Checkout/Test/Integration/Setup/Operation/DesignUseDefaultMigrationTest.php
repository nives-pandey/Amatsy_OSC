<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Integration\Setup\Operation;

use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Setup\Operation\DesignUseDefaultMigration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class DesignUseDefaultMigrationTest
 *
 * @see DesignUseDefaultMigration
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DesignUseDefaultMigrationTest extends TestCase
{
    const CLASSIC_DESIGN = '0';
    const MODERN_DESIGN = '1';

    const ONE_COLUMN = '1column';
    const TWO_COLUMNS = '2columns';
    const THREE_COLUMNS = '3columns';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $configTableName;

    /**
     * @var array
     */
    private $scopeConfigDataBackup = [];

    public function setUp(): void
    {
        /** @var ModuleDataSetupInterface $moduleDataSetup */
        $this->moduleDataSetup = Bootstrap::getObjectManager()->get(ModuleDataSetupInterface::class);

        $this->connection = $this->moduleDataSetup->getConnection();
        $this->configTableName = $this->moduleDataSetup->getTable('core_config_data');

        // backup old values, then drop them and save new values
        $this->scopeConfigDataBackup = $this->connection->fetchAll(
            $this->connection->select()
                ->from($this->configTableName)
                ->order('config_id')
        );
        $this->connection->delete(
            $this->configTableName
        );
    }

    public function tearDown(): void
    {
        $this->connection->delete(
            $this->configTableName
        );
        $this->saveValues($this->scopeConfigDataBackup);
    }

    /**
     * @covers \Amasty\Checkout\Setup\Operation\DesignUseDefaultMigration::execute
     * @dataProvider testExecuteDataProvider
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @param array $configFixtures
     * @param array $results
     */
    public function testExecute($configFixtures, $results)
    {
        $this->saveValues($configFixtures);

        /** @var DesignUseDefaultMigration $designUseDefaultMigration */
        $designUseDefaultMigration = Bootstrap::getObjectManager()->get(
            DesignUseDefaultMigration::class
        );

        $designUseDefaultMigration->execute($this->moduleDataSetup);

        foreach ($results as $resultRow) {
            $select = $this->connection
                ->select()
                ->from($this->configTableName, ['value'])
                ->where('path = ?', $resultRow['path'])
                ->where('scope = ?', $resultRow['scope'])
                ->where('scope_id = ?', $resultRow['scope_id']);
            $valueFromDb = $this->connection->fetchOne($select);
            $this->assertEquals($resultRow['value'], $valueFromDb, 'Config value by path ' . $resultRow['path']);
        }

        $designBlockPath = Config::PATH_PREFIX . Config::DESIGN_BLOCK;

        $countSelect = $this->connection->select()
            ->from($this->configTableName, new \Zend_Db_Expr('COUNT(*)'))
            ->where(
                'path IN(?)',
                [
                    $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                    $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT,
                    $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                ]
            );

        $count = $this->connection->fetchOne($countSelect);
        $this->assertEquals(count($results), $count, 'Configurations count doesn\'t match');
    }

    /**
     * @param array $configRows
     */
    private function saveValues($configRows)
    {
        if (empty($configRows)) {
            return;
        }
        $this->connection->insertMultiple(
            $this->configTableName,
            $configRows
        );
    }

    public function testExecuteDataProvider()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $storeCode = 'fixturestore';
        $store->load($storeCode);
        $storeId = $store->getId();
        $websiteId = $store->getWebsiteId();

        $designBlockPath = Config::PATH_PREFIX . Config::DESIGN_BLOCK;
        return [

            'emptyConfig' => [
                [
                ],

                [
                ]
            ],

            'onlyLayout' => [
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                        'value' => self::THREE_COLUMNS,
                    ],

                ],

                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                        'value' => self::MODERN_DESIGN,
                    ],

                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                        'value' => self::THREE_COLUMNS,
                    ]
                ]
            ],

            'fieldForDelete' => [
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT,
                        'value' => self::THREE_COLUMNS,
                    ],

                ],

                [
                ]
            ],

            'scopesConfig' => [
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                        'value' => self::CLASSIC_DESIGN,
                    ],
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                        'value' => self::THREE_COLUMNS,
                    ],

                    //website
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                        'value' => self::MODERN_DESIGN,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT,
                        'value' => self::TWO_COLUMNS,
                    ],

                    // store
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                        'value' => self::TWO_COLUMNS,
                    ],

                ],

                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                        'value' => self::CLASSIC_DESIGN,
                    ],

                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT,
                        'value' => self::THREE_COLUMNS,
                    ],

                    //website
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                        'value' => self::MODERN_DESIGN,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                        'value' => self::THREE_COLUMNS,
                    ],

                    // store
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                        'value' => self::MODERN_DESIGN,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                        'value' => self::TWO_COLUMNS,
                    ],
                ]
            ],
        ];
    }
}
