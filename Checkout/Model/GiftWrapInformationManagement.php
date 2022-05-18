<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\FeeRepositoryInterface;
use Amasty\Checkout\Api\GiftWrapInformationManagementInterface;
use Amasty\Checkout\Model\FeeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GiftWrapInformationManagement
 */
class GiftWrapInformationManagement implements GiftWrapInformationManagementInterface
{
    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var FeeRepositoryInterface
     */
    protected $feeRepository;

    /**
     * @var Fee
     */
    protected $feeFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository,
        FeeRepositoryInterface $feeRepository,
        FeeFactory $feeFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
        $this->cartRepository = $cartRepository;
        $this->feeRepository = $feeRepository;
        $this->feeFactory = $feeFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int  $cartId
     * @param bool $checked
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function update($cartId, $checked)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($cartId);

        $fee = $this->feeRepository->getByQuoteId($quote->getId());

        if ($checked && !$fee->getId()) {
            $baseAmount = $this->scopeConfig->getValue(
                'amasty_checkout/gifts/gift_wrap_fee',
                ScopeInterface::SCOPE_STORE
            );

            $store = $this->storeManager->getStore();
            $rate = $store->getBaseCurrency()->getRate($store->getCurrentCurrency());

            $fee = $this->feeFactory->create(['data' => [
                'quote_id' => $quote->getId(),
                'amount' => $baseAmount * $rate,
                'base_amount' => $baseAmount,
            ]])->setDataChanges(true);

            $this->feeRepository->save($fee);
        } elseif (!$checked && $fee->getId()) {
            $this->feeRepository->delete($fee);
        }

        $quote->collectTotals();

        return $this->cartTotalRepository->get($cartId);
    }
}
