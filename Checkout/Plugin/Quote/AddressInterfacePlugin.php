<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Quote;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Fix for extensionAttributes type.
 * When we call shipping estimate (e.g. \Magento\Quote\Model\ShippingMethodManagement::estimateByExtendedAddress)
 * and we have extension_attributes in $address - that attributes will be converted to array, and code, which use
 * $address->getExtensionAttributes()->someMethod() will be failed
 */
class AddressInterfacePlugin
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    public function __construct(ExtensionAttributesFactory $extensionAttributesFactory)
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * @param AddressInterface $subject
     * @param \Magento\Framework\Api\ExtensionAttributesInterface|array|null $result
     * @return \Magento\Framework\Api\ExtensionAttributesInterface|null
     */
    public function afterGetExtensionAttributes(AddressInterface $subject, $result)
    {
        if ($result && is_array($result)) {
            $result = $this->extensionAttributesFactory->create(get_class($subject), $result);
            $subject->setData(AddressInterface::EXTENSION_ATTRIBUTES_KEY, $result);
        }

        return $result;
    }
}
