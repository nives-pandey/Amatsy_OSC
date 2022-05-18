<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * @api
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @codingStandardsIgnoreStart
 */
interface QuoteManagementInterface
{
    /**
     * @param int $cartId
     * @param AddressInterface|null $shippingAddressFromData
     * @param AddressInterface|null $newCustomerBillingAddress
     * @param string|null $selectedPaymentMethod
     * @param string|null $selectedShippingRate
     * @param string|null $validatedEmailValue
     *
     * @return boolean
     */
    public function saveInsertedInfo(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null,
        $selectedPaymentMethod = null,
        $selectedShippingRate = null,
        $validatedEmailValue = null
    );
}
