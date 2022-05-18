<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\GuestQuoteManagementInterface;
use Amasty\Checkout\Api\QuoteManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;

/**
 * Save checkout guest statistic
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GuestQuoteManagement implements GuestQuoteManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteManagementInterface $quoteManagement,
        LoggerInterface $logger
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteManagement = $quoteManagement;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function saveInsertedInfo(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null,
        $selectedPaymentMethod = null,
        $selectedShippingRate = null,
        $validatedEmailValue = null
    ) {
        try {
            /** @var $quoteIdMask QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        } catch (\Exception $e) {
            $this->logger->debug($e);

            return false;
        }

        return $this->quoteManagement->saveInsertedInfo(
            $quoteIdMask->getQuoteId(),
            $shippingAddressFromData,
            $newCustomerBillingAddress,
            $selectedPaymentMethod,
            $selectedShippingRate,
            $validatedEmailValue
        );
    }
}
