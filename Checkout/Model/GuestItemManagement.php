<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\GuestItemManagementInterface;
use Amasty\Checkout\Api\ItemManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\AddressInterface;

class GuestItemManagement implements GuestItemManagementInterface
{
    /** @var QuoteIdMaskFactory */
    protected $quoteIdMaskFactory;
    /**
     * @var ItemManagementInterface
     */
    protected $itemManagement;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ItemManagementInterface $itemManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->itemManagement = $itemManagement;
    }

    /**
     * @inheritdoc
     */
    public function remove($cartId, $itemId, AddressInterface $address)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->itemManagement->remove(
            $quoteIdMask->getQuoteId(),
            $itemId,
            $address
        );
    }

    /**
     * @inheritdoc
     */
    public function update($cartId, $itemId, $formData)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->itemManagement->update(
            $quoteIdMask->getQuoteId(),
            $itemId,
            $formData
        );
    }
}
