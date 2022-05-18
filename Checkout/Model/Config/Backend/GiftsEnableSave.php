<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Backend;

use Amasty\Checkout\Model\Config as CheckoutConfigModel;
use Magento\Config\Model\ResourceModel\Config as ConfigData;

class GiftsEnableSave extends \Magento\Framework\App\Config\Value
{
    /**
     * @var ConfigData
     */
    private $configData;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ConfigData $configData,
        array $data = []
    ) {
        $this->configData = $configData;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Actions after save
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->getValue() == 1) {
            $configurationsForDisable = [
                CheckoutConfigModel::PATH_PREFIX . CheckoutConfigModel::GIFTS . CheckoutConfigModel::GIFT_WRAP,
                'sales/gift_options/allow_order',
                'sales/gift_options/allow_items'
            ];
            foreach ($configurationsForDisable as $path) {
                $this->configData->saveConfig(
                    $path,
                    0,
                    $this->getScope(),
                    $this->getScopeId()
                );
            }
        }

        return parent::afterSave();
    }
}
