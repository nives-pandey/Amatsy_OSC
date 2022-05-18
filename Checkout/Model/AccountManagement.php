<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\AdditionalFieldsManagementInterface;
use Amasty\Checkout\Api\QuotePasswordsRepositoryInterface;
use Amasty\Checkout\Model\QuotePasswordsFactory;
use Amasty\Checkout\Model\Sales\OrderCustomerExtractor;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\GroupManagementInterface;

class AccountManagement implements \Amasty\Checkout\Api\AccountManagementInterface
{
    /**
     * Array of Carrier codes for which the shipping address needs to be replaced with the billing address
     */
    const CARRIER_CODE_CHANGE_SHIPPING = ['amstorepickup'];

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var OrderCustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuotePasswordsRepositoryInterface
     */
    private $quotePasswordsRepository;

    /**
     * @var \Amasty\Checkout\Model\QuotePasswordsFactory
     */
    private $quotePasswordsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var AdditionalFieldsManagementInterface
     */
    private $fieldsManagement;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var CustomerValidator
     */
    private $customerValidator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        OrderCustomerExtractor $customerExtractor,
        Config $config,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuotePasswordsRepositoryInterface $quotePasswordsRepository,
        QuotePasswordsFactory $quotePasswordsFactory,
        LoggerInterface $logger,
        EncryptorInterface $encryptor,
        AdditionalFieldsManagementInterface $fieldsManagement,
        TimezoneInterface $timezone,
        CustomerValidator $customerValidator,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        GroupManagementInterface $groupManagement
    ) {
        $this->orderRepository = $orderRepository;
        $this->accountManagement = $accountManagement;
        $this->customerExtractor = $customerExtractor;
        $this->config = $config;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quotePasswordsRepository = $quotePasswordsRepository;
        $this->quotePasswordsFactory = $quotePasswordsFactory;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->fieldsManagement = $fieldsManagement;
        $this->timezone = $timezone;
        $this->customerValidator = $customerValidator;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->groupManagement = $groupManagement;
    }

    /**
     * @inheritdoc
     */
    public function createAccount($order)
    {
        /** @var QuotePasswords $passwordQuote */
        $passwordQuote = $this->getPasswordQuote($order->getQuoteId());

        if ($this->config->getAdditionalOptions('create_account') === '2' && $passwordQuote->hasData()) {
            $this->setCustomerDob($order);

            /** @var Customer $customer */
            $customer = $this->customerExtractor->extract($order);
            $this->setCustomerInformation($customer, $order);

            if (!$customer->getId()
                && $this->accountManagement->isEmailAvailable($customer->getEmail())
                && $this->customerValidator->validateByDataObject($customer)
            ) {
                /** @var Customer $account */
                $account = $this->accountManagement->createAccountWithPasswordHash(
                    $customer,
                    $passwordQuote->getPasswordHash()
                );

                $this->eventManager->dispatch(
                    'customer_register_success',
                    [
                        'customer' => $account,
                        'amasty_checkout_register' => true
                    ]
                );

                $order->setCustomerId($account->getId());
                $order->setCustomerGroupId($account->getGroupId());
                $order->setCustomerIsGuest(0);
                $this->orderRepository->save($order);
                $this->deletePassword($order);

                return $account;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function savePassword($cartId, $password)
    {
        if ($this->config->getAdditionalOptions('create_account') === '2' && $password) {
            try {
                /** @var QuoteIdMask $quoteIdMask */
                $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
                /** @var QuotePasswords $quotePassword */
                $quotePassword = $this->getPasswordQuote($quoteIdMask->getQuoteId());

                $passwordHash = $this->createPasswordHash($password);
                $quotePassword->setPasswordHash($passwordHash);
                $quotePassword->setQuoteId($quoteIdMask->getQuoteId());

                $this->quotePasswordsRepository->save($quotePassword);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deletePassword($order)
    {
        if ($order) {
            try {
                $passwordQuote = $this->quotePasswordsRepository->getByQuoteId($order->getQuoteId());
                $this->quotePasswordsRepository->delete($passwordQuote);
            } catch (\Exception $exception) {
                return true;
            }
        }

        return true;
    }

    /**
     * @param int $quoteId
     *
     * @return QuotePasswords
     */
    private function getPasswordQuote($quoteId)
    {
        try {
            $quotePassword = $this->quotePasswordsRepository->getByQuoteId($quoteId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $quotePassword = $this->quotePasswordsFactory->create();
        }

        /** @var QuotePasswords $quotePassword */
        return $quotePassword;
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    private function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * @param OrderInterface $order
     */
    private function setCustomerDob(OrderInterface $order)
    {
        /** @var \Amasty\Checkout\Model\AdditionalFields $fields */
        $fields = $this->fieldsManagement->getByQuoteId($order->getQuoteId());

        if ($fields->getDateOfBirth()) {
            $customerDob = $this->timezone->date($fields->getDateOfBirth())
                ->format(DateTime::DATETIME_PHP_FORMAT);
            /** @var \Magento\Sales\Model\Order\Address $billingAddress */
            $billingAddress = $order->getBillingAddress();
            $billingAddress->setCustomerDob($customerDob);
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function isNeedChangeShippingAddress(OrderInterface $order)
    {
        $carrierCode = $order->getShippingMethod(true)->getCarrierCode();

        return in_array($carrierCode, self::CARRIER_CODE_CHANGE_SHIPPING);
    }

    /**
     * @param Customer $customer
     */
    private function replacementShippingForBilling(Customer $customer)
    {
        $billingAddress = null;
        $addresses = $customer->getAddresses();

        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $billingAddress = $address;
                break;
            }
        }

        if ($billingAddress !== null) {
            $billingAddress->setIsDefaultShipping(true);
            $customer->setAddresses([$billingAddress]);
        }
    }

    /**
     * @param Customer $customer
     * @param OrderInterface $order
     */
    private function setCustomerInformation(Customer $customer, OrderInterface $order)
    {
        //compatible with store pickups
        if ($this->isNeedChangeShippingAddress($order)) {
            $this->replacementShippingForBilling($customer);
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $this->storeManager->setCurrentStore(null);
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        // Associate tax_vat with customer
        if (!$customer->getTaxvat() && $order->getShippingAddress()) {
            $customer->setTaxvat($order->getShippingAddress()->getVatId());
        }

        // Associate group_id with customer
        if (!$customer->getGroupId()) {
            $groupId = $this->groupManagement->getDefaultGroup($customer->getStoreId())->getId();
            $customer->setGroupId($groupId);
        }
    }
}
