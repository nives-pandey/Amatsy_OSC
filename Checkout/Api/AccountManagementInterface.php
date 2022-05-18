<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;

interface AccountManagementInterface
{
    /**
     * @param OrderInterface $order
     *
     * @return bool|CustomerInterface
     */
    public function createAccount($order);

    /**
     * @param string $cartId
     * @param string $password
     *
     * @return boolean
     */
    public function savePassword($cartId, $password);

    /**
     * @param OrderInterface $order
     *
     * @return boolean
     */
    public function deletePassword($order);
}
