<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Model\Config;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Message\ManagerInterface;

class Subscription
{
    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        ManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        EmailValidator $emailValidator,
        Config $configProvider,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement
    ) {

        $this->subscriberFactory = $subscriberFactory;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->emailValidator = $emailValidator;
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * @param string|null $email
     */
    public function subscribe($email = null)
    {
        $status = '';

        if ($email === null) {
            $email = $this->checkoutSession->getQuote()->getCustomerEmail();
        }

        try {
            if ($this->validateEmailFormat($email)
                && $this->validateGuestSubscription()
                && $this->validateEmailAvailable($email)
            ) {
                $subscriber = $this->subscriberFactory->create()->loadByEmail($email);

                if (!$subscriber->getId() || (int)$subscriber->getStatus() === Subscriber::STATUS_UNSUBSCRIBED) {
                    $status = $this->subscriberFactory->create()->subscribe($email);
                }
            }

            if ($status == Subscriber::STATUS_NOT_ACTIVE) {
                $this->messageManager->addSuccessMessage(__('The confirmation request has been sent.'));
            } elseif (!empty($status)) {
                $this->messageManager->addSuccessMessage(__('Thank you for your subscription.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('There was a problem with the subscription: %1', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong with the subscription.'));
        }
    }

    /**
     * @return bool
     */
    private function validateGuestSubscription()
    {
        return $this->configProvider->allowGuestSubscribe() || $this->customerSession->isLoggedIn();
    }

    /**
     * @param string $email
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateEmailAvailable($email)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        return $this->customerSession->getCustomerDataObject()->getEmail() === $email
            || $this->customerAccountManagement->isEmailAvailable($email, $websiteId);
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    private function validateEmailFormat($email)
    {
        return $this->emailValidator->isValid($email);
    }
}
