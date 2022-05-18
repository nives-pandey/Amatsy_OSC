<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

namespace Amasty\Checkout\Api\Data;

interface TotalsInterface
{
    const TOTALS = 'totals';
    const SHIPPING = 'shipping';
    const PAYMENT = 'payment';

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    public function getShipping();

    /**
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[] Array of payment methods.
     */
    public function getPayment();
}
