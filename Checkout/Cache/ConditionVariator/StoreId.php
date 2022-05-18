<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Cache\ConditionVariator;

use Amasty\Checkout\Api\CacheKeyPartProviderInterface;

/**
 * Add cache variation for each store ID
 */
class StoreId implements CacheKeyPartProviderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getKeyPart()
    {
        return 'store=' . $this->storeManager->getStore()->getId();
    }
}
