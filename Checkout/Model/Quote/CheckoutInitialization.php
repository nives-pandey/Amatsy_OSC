<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Quote;

use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\FieldsDefaultProvider;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * Initialize additional quote data to avoid extra requests on storefront.
 *
 * @since 3.0.0
 * @since 3.0.5 set default payment method
 */
class CheckoutInitialization implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $checkoutConfig;

    /**
     * @var FieldsDefaultProvider
     */
    private $defaultProvider;

    /**
     * @var ServiceOutputProcessor
     */
    private $outputProcessor;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Shipping\AddressMethods
     */
    private $addressMethods;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodList;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalsRepository;

    public function __construct(
        Config $checkoutConfig,
        FieldsDefaultProvider $defaultProvider,
        ServiceOutputProcessor $outputProcessor,
        CartRepositoryInterface $quoteRepository,
        Shipping\AddressMethods $addressMethods,
        PaymentMethodManagementInterface $paymentMethodList,
        CartTotalRepositoryInterface $cartTotalsRepository
    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->defaultProvider = $defaultProvider;
        $this->outputProcessor = $outputProcessor;
        $this->quoteRepository = $quoteRepository;
        $this->addressMethods = $addressMethods;
        $this->paymentMethodList = $paymentMethodList;
        $this->cartTotalsRepository = $cartTotalsRepository;
    }

    /**
     * Get shipping
     *
     * @param Quote $quote
     *
     * @return array|object
     */
    public function getShippingMethods($quote)
    {
        $methods = $this->addressMethods->getShippingMethods($this->getShippingAddress($quote));

        return $this->outputProcessor->convertValue(
            $methods,
            '\Magento\Quote\Api\Data\ShippingMethodInterface[]'
        );
    }

    /**
     * Resolve quote shipping address.
     *
     * @param Quote $quote
     *
     * @return Address
     */
    public function getShippingAddress($quote)
    {
        if ($quote->getCustomerId() && $shippingAddress = $this->getCustomerShippingAddress($quote)) {
            return $shippingAddress;
        }

        /** @var Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress->getId() && $shippingAddress->getCountryId()) {
            return $shippingAddress;
        }

        $shippingAddress->addData($this->defaultProvider->getDefaultData());
        if (!$shippingAddress->getCountryId()) {
            $shippingAddress->setCountryId($this->checkoutConfig->getDefaultCountryId());
        }

        return $shippingAddress;
    }

    /**
     * Check quote shipping address
     * convert customer address into shipping if quote shipping is not selected.
     * false if customer doesn't have any address.
     *
     * @param Quote $quote
     *
     * @return Address|false
     */
    private function getCustomerShippingAddress($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress->getId() && $shippingAddress->getCustomerAddressId()) {
            return $shippingAddress;
        }

        $customerAddress = $this->getCustomerAddress($quote->getCustomer());

        if (!$customerAddress) {
            return false;
        }

        $addressByCustomer = $quote->getShippingAddressByCustomerAddressId($customerAddress->getId());
        if ($addressByCustomer) {
            $quote->setShippingAddress($addressByCustomer);

            return $addressByCustomer;
        }

        $shippingAddress->importCustomerAddressData($customerAddress);

        return $shippingAddress;
    }

    /**
     * Get default of first customer address.
     *
     * @param CustomerInterface $customer
     *
     * @return AddressInterface|false
     */
    private function getCustomerAddress($customer)
    {
        $customer->getDefaultShipping();
        $addresses = $customer->getAddresses();
        if (empty($addresses)) {
            return false;
        }

        foreach ($addresses as $customerAddress) {
            if ($customerAddress->isDefaultShipping()) {
                return $customerAddress;
            }
        }

        return reset($addresses);
    }

    /**
     * Set initial values: shipping address, shipping method, payment method.
     * For avoid extra request on frontend while loading.
     * Note: $quote can be without id (new entity). For better optimization, don't save quote early.
     *
     * @param CartInterface|Quote $quote
     */
    public function initializeShipping(CartInterface $quote)
    {
        $shippingAddress = $this->getShippingAddress($quote);

        $default = $this->checkoutConfig->getDefaultPaymentMethod();
        if ($default && !$shippingAddress->getPaymentMethod()) {
            foreach ($this->getPaymentMethods($quote) as $paymentMethod) {
                if ($paymentMethod->getCode() === $default) {
                    $payment = $quote->getPayment();
                    $shippingAddress->setPaymentMethod($paymentMethod->getCode());
                    $payment->setMethod($paymentMethod->getCode());
                    break;
                }
            }
        }

        $this->addressMethods->processShippingAssignment($quote, $shippingAddress);
    }

    /**
     * @param CartInterface $quote
     *
     * @return array
     */
    public function getPaymentArray(CartInterface $quote): array
    {
        $methodsArray = $this->outputProcessor->convertValue(
            $this->getPaymentMethods($quote),
            PaymentMethodInterface::class . '[]'
        );

        return [
            PaymentDetailsInterface::PAYMENT_METHODS => $methodsArray,
            PaymentDetailsInterface::TOTALS => $this->getTotalsArray((int)$quote->getid())
        ];
    }

    /**
     * Save quote with initial data
     *
     * @param CartInterface $quote
     */
    public function saveInitial(CartInterface $quote)
    {
        $quote->setItems([]);
        $this->quoteRepository->save($quote);
    }

    /**
     * @param CartInterface $quote
     *
     * @return MethodInterface[]
     */
    public function getPaymentMethods(CartInterface $quote): array
    {
        if (!isset($this->payment)) {
            $this->payment = $this->paymentMethodList->getList($quote->getId());
        }

        return $this->payment;
    }

    /**
     * @param int $quoteId
     *
     * @return array
     */
    public function getTotalsArray(int $quoteId): array
    {
        $totals = $this->cartTotalsRepository->get($quoteId);

        return $this->outputProcessor->convertValue($totals, TotalsInterface::class);
    }
}
