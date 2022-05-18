<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api\Data;

interface OrderCustomFieldsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'entity_id';
    const ORDER_ID = 'order_id';
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
    public function getOrderId();

    /**
     * @param int $id
     *
     * @return \Amasty\Checkout\Api\Data\OrderCustomFieldsInterface
     */
    public function setOrderId($id);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return \Amasty\Checkout\Api\Data\OrderCustomFieldsInterface
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getBillingValue();

    /**
     * @param string $value
     *
     * @return \Amasty\Checkout\Api\Data\OrderCustomFieldsInterface
     */
    public function setBillingValue($value);

    /**
     * @return string|null
     */
    public function getShippingValue();

    /**
     * @param string $value
     *
     * @return \Amasty\Checkout\Api\Data\OrderCustomFieldsInterface
     */
    public function setShippingValue($value);
}
