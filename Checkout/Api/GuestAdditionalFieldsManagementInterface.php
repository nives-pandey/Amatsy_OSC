<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

interface GuestAdditionalFieldsManagementInterface
{
    /**
     * @param string $cartId
     * @param \Amasty\Checkout\Api\Data\AdditionalFieldsInterface $fields
     *
     * @return bool
     */
    public function save($cartId, $fields);
}
