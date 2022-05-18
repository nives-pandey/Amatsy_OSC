<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup\Operation;

use Amasty\Base\Model\Serializer;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Config\Model\ResourceModel\Config\DataFactory;

/**
 * Class UpgradeDataTo203
 */
class UpgradeDataTo203
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataFactory
     */
    private $resourceFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        CollectionFactory $collectionFactory,
        DataFactory $resourceFactory,
        Serializer $serializer
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceFactory = $resourceFactory;
        $this->serializer = $serializer;
    }

    public function execute()
    {
        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $configCollection */
        $configCollection = $this->collectionFactory->create();
        /** @var \Magento\Config\Model\ResourceModel\Config\Data $configResource */
        $configResource = $this->resourceFactory->create();
        $blocksInfo = $configCollection->addPathFilter('amasty_checkout/block_names')->getItems();

        if ($blocksInfo) {
            /** @var \Magento\Framework\App\Config\Value $blockInfo */
            foreach ($blocksInfo as $blockInfo) {
                if ($blockData = $blockInfo->getData()) {
                    $value = ['value' => $blockData['value'], 'sort_order' => 0];
                    $blockData['value'] = $this->serializer->serialize($value);
                    $blockInfo->setData($blockData);
                    $configResource->save($blockInfo);
                }
            }
        }
    }
}
