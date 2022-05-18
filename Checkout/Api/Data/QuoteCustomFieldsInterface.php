<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api\Data;

interface QuoteCustomFieldsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'entity_id';
    const QUOTE_ID = 'quote_id';
    const NAME = 'name';
    const BILLING_VALUE = 'billing_value';
    const SHIPPING_VALUE = 'shipping_value';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getQuoteId();

    /**
     * @param int $id
     *
     * @return \Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface
     */
    public function setQuoteId($id);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return \Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getBillingValue();

    /**
     * @param string $value
     *
     * @return \Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface
     */
    public function setBillingValue($value);

    /**
     * @return string|null
     */
    public function getShippingValue();

    /**
     * @param string $value
     *
     * @return \Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface
     */
    public function setShippingValue($value);
}
