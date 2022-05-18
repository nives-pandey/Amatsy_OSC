<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Cache\ConditionVariator;

use Amasty\Checkout\Api\CacheKeyPartProviderInterface;

/**
 * Add cache variation for each customer group
 */
class CustomerGroup implements CacheKeyPartProviderInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(\Magento\Customer\Model\Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @return string
     */
    public function getKeyPart()
    {
        return 'cusGroup=' . $this->customerSession->getCustomerGroupId();
    }
}
