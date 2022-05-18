<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

use Magento\Quote\Api\Data\AddressInterface;

interface ItemManagementInterface
{
    /**
     * @param int              $cartId
     * @param int              $itemId
     * @param AddressInterface $address
     *
     * @return \Amasty\Checkout\Api\Data\TotalsInterface|boolean
     */
    public function remove($cartId, $itemId, AddressInterface $address);

    /**
     * @param int              $cartId
     * @param int              $itemId
     * @param string           $formData
     *
     * @return \Amasty\Checkout\Api\Data\TotalsInterface|boolean
     */
    public function update($cartId, $itemId, $formData);
}
