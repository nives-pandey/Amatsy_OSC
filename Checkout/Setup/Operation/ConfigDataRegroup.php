<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Config\Model\ResourceModel\Config\DataFactory;

/**
 * @since 2.10.0
 */
class ConfigDataRegroup
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataFactory
     */
    private $resourceFactory;

    const PATH_CONFIG_BEFORE = 'amasty_checkout/design/place_button/layout';

    const PATH_CONFIG_AFTER = 'amasty_checkout/design/place_button_layout';

    public function __construct(
        CollectionFactory $collectionFactory,
        DataFactory $resourceFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceFactory = $resourceFactory;
    }

    public function execute()
    {
        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $configCollection */
        $configCollection = $this->collectionFactory->create();
        /** @var \Magento\Config\Model\ResourceModel\Config\Data $configResource */
        $configResource = $this->resourceFactory->create();
        $blocksInfo = $configCollection
            ->addFieldToFilter('path', self::PATH_CONFIG_BEFORE)
            ->getItems();

        foreach ($blocksInfo as $blocksInfoItem) {
            if ($blocksInfoItem) {
                $blocksInfoItem['path'] = self::PATH_CONFIG_AFTER;
                $configResource->save($blocksInfoItem);
            }
        }
    }
}
