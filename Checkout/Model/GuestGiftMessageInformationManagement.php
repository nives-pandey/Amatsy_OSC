<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\GiftMessageInformationManagementInterface;
use Amasty\Checkout\Api\GuestGiftMessageInformationManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestGiftMessageInformationManagement
 */
class GuestGiftMessageInformationManagement implements GuestGiftMessageInformationManagementInterface
{
    /** @var QuoteIdMaskFactory */
    protected $quoteIdMaskFactory;
    /**
     * @var GiftMessageInformationManagementInterface
     */
    protected $giftMessageInformationManagement;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        GiftMessageInformationManagementInterface $giftMessageInformationManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->giftMessageInformationManagement = $giftMessageInformationManagement;
    }

    /**
     * @inheritdoc
     */
    public function update($cartId, $giftMessage)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->giftMessageInformationManagement->update(
            $quoteIdMask->getQuoteId(),
            $giftMessage
        );
    }
}
