<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Plugin\Quote\Model\QuoteRepository;

use Amasty\Checkout\Model\Quote\CustomFieldItemsProvider;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository\LoadHandler;

class LoadHandlerPlugin
{
    /**
     * @var CustomFieldItemsProvider
     */
    private $customFieldItemsProvider;

    public function __construct(
        CustomFieldItemsProvider $customFieldItemsProvider
    ) {
        $this->customFieldItemsProvider = $customFieldItemsProvider;
    }

    /**
     * Load Custom Quote Address Attribute values.
     *
     * @param LoadHandler $subject
     * @param Quote|CartInterface $quote
     *
     * @return Quote|CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(LoadHandler $subject, CartInterface $quote): CartInterface
    {
        $fields = $this->customFieldItemsProvider->getItemsByQuoteId((int)$quote->getId());
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        foreach ($fields as $field) {
            if (!$field->getId()) {
                continue;
            }

            $billingAddress->setCustomAttribute($field->getName(), $field->getBillingValue());
            $shippingAddress->setCustomAttribute($field->getName(), $field->getShippingValue());
        }

        return $quote;
    }
}
