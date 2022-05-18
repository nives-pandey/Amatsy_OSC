<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\GiftWrapInformationManagementInterface;
use Amasty\Checkout\Api\GuestGiftWrapInformationManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestGiftWrapInformationManagement
 */
class GuestGiftWrapInformationManagement implements GuestGiftWrapInformationManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var GiftWrapInformationManagementInterface
     */
    protected $giftWrapInformationManagement;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        GiftWrapInformationManagementInterface $giftWrapInformationManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->giftWrapInformationManagement = $giftWrapInformationManagement;
    }

    /**
     * @inheritdoc
     */
    public function update($cartId, $checked)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->giftWrapInformationManagement->update(
            $quoteIdMask->getQuoteId(),
            $checked
        );
    }
}
