<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Quote;

use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Amasty\Checkout\Helper\Address as AddressHelper;

class Address
{
    /**
     * @var AddressHelper
     */
    protected $addressHelper;

    public function __construct(
        AddressHelper $addressHelper
    ) {
        $this->addressHelper = $addressHelper;
    }

    /**
     * Fix custom attributes conversation error.
     * Remove label from value to avoid implement
     *
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param array|string $key
     * @param array|string|null $value
     *
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetData(\Magento\Quote\Model\Quote\Address $subject, $key, $value = null)
    {
        if (is_string($key) && in_array($key, CustomFieldsConfigInterface::CUSTOM_FIELDS_ARRAY, true)) {
            if (is_array($value)) {
                $value = $value['value'];
            }

            $subject->setCustomAttribute($key, $value);

            return [$key, $value];
        }

        return null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterAddData(
        \Magento\Quote\Model\Quote\Address $subject,
        $result
    ) {
        $this->addressHelper->fillEmpty($subject);

        return $result;
    }
}
