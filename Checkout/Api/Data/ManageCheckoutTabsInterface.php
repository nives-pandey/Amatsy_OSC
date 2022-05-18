<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api\Data;

interface ManageCheckoutTabsInterface
{
    /**#@+
     * Constants defined for config values
     */
    const CUSTOMER_INFO_TAB = 'customer';
    const ORDER_SUMMARY_TAB = 'summary';
    const PAYMENT_METHOD_TAB = 'payment';
    const SHIPPING_METHOD_TAB = 'shipping';
    /**#@-*/
}
