<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

namespace Amasty\Checkout\Api\Data;

interface FeeInterface
{
    const ENTITY_ID = 'id';
    const ORDER_ID = 'order_id';
    const QUOTE_ID = 'quote_id';
    const AMOUNT = 'amount';
    const BASE_AMOUNT = 'base_amount';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @return int|null
     */
    public function getQuoteId();

    /**
     * @return int
     */
    public function getAmount();

    /**
     * @return int
     */
    public function getBaseAmount();

    /**
     * @param int $id
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setOrderId($id);

    /**
     * @param int $id
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setQuoteId($id);

    /**
     * @param int $amount
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setAmount($amount);

    /**
     * @param int $amount
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setBaseAmount($amount);
}
