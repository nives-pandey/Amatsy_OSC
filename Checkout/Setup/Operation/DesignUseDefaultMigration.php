<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Setup\Operation\Utils\ConfigManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class DesignUseDefaultMigration
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $configTableName = $setup->getTable('core_config_data');

        $designBlockPath = Config::PATH_PREFIX . Config::DESIGN_BLOCK;

        $select = $connection->select()
            ->from($configTableName)
            ->where('path IN(?)', [
                $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT,
                $designBlockPath . Config::FIELD_CHECKOUT_LAYOUT_MODERN
            ]);

        $configRows = $connection->fetchAll($select);
        $scopedConfig = $this->configManager->reorderConfigRowsToScopedConfig($configRows);
        $scopedConfig = $this->configManager->sortConfigGlobalScopeFirst($scopedConfig);

        foreach ($scopedConfig as $scopeKey => &$scopeData) {
            list($scope, $scopeId) = explode('_', $scopeKey);

            $design = $scopeData[Config::FIELD_CHECKOUT_DESIGN] ?? null;
            $hasDesignInDb = true;
            if ($design === null) {
                $hasDesignInDb = false;
                $design = $this->configManager->getParentScopeValue(
                    $scopedConfig,
                    $scope,
                    $scopeId,
                    Config::FIELD_CHECKOUT_DESIGN
                );
                if ($design === null) {
                    $design = $this->configManager->getDefaultValue(
                        Config::FIELD_CHECKOUT_DESIGN
                    );
                }
            }

            if ((int)$design === 0) {
                $layoutField = Config::FIELD_CHECKOUT_LAYOUT;
                $fieldToDelete = Config::FIELD_CHECKOUT_LAYOUT_MODERN;
            } else {
                $layoutField = Config::FIELD_CHECKOUT_LAYOUT_MODERN;
                $fieldToDelete = Config::FIELD_CHECKOUT_LAYOUT;
            }

            if (isset($scopeData[$fieldToDelete])) {
                $scopeData[$fieldToDelete] = null;
                $connection->delete($configTableName, [
                    'path = ?' => $designBlockPath . $fieldToDelete,
                    'scope = ?' => $scope,
                    'scope_id = ?' => $scopeId

                ]);
            }

            if (!empty($scopeData[$layoutField]) && !$hasDesignInDb) {
                $connection->insert(
                    $configTableName,
                    [
                        'path' => $designBlockPath . Config::FIELD_CHECKOUT_DESIGN,
                        'scope' => $scope,
                        'scope_id' => $scopeId,
                        'value' => $design
                    ]
                );
            }

            if (empty($scopeData[$layoutField]) && $hasDesignInDb) {
                $layoutValue = $this->configManager->getParentScopeValue(
                    $scopedConfig,
                    $scope,
                    $scopeId,
                    $layoutField
                );

                $layoutValue === null && $layoutValue = $this->configManager->getDefaultValue($layoutField);
                $connection->insert(
                    $configTableName,
                    [
                        'path' => $designBlockPath . $layoutField,
                        'scope' => $scope,
                        'scope_id' => $scopeId,
                        'value' => $layoutValue
                    ]
                );
            }

        }
    }
}
