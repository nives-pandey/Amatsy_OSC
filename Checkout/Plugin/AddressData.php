<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin;

use Amasty\Checkout\Helper\Address;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;

/**
 * area webapi_rest
 */
class AddressData
{
    /**
     * @var Address
     */
    private $addressHelper;

    public function __construct(
        Address $addressHelper
    ) {
        $this->addressHelper = $addressHelper;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return array
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        foreach ([$addressInformation->getShippingAddress(), $addressInformation->getBillingAddress()] as $address) {
            if ($address) {
                $this->addressHelper->fillEmpty($address);
            }
        }

        return [$cartId, $addressInformation];
    }
}
