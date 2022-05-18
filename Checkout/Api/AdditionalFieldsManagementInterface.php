<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

interface AdditionalFieldsManagementInterface
{
    /**
     * @param int $cartId
     * @param \Amasty\Checkout\Api\Data\AdditionalFieldsInterface $fields
     *
     * @return bool
     */
    public function save($cartId, $fields);

    /**
     * @param int $quoteId
     *
     * @return \Amasty\Checkout\Api\Data\AdditionalFieldsInterface|\Amasty\Checkout\Model\AdditionalFields
     */
    public function getByQuoteId($quoteId);
}
