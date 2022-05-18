<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config;

class CheckoutBlocksProvider
{
    /**
     * @return array
     */
    public function getDefaultBlockTitles()
    {
        return [
            'shipping_address' => __('Shipping Address'),
            'shipping_method' => __('Shipping Method'),
            'delivery' => __('Delivery'),
            'payment_method' => __('Payment Method'),
            'summary' => __('Order Summary'),
        ];
    }
}
