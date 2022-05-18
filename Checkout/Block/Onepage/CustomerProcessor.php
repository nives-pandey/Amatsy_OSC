<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Onepage;

use Amasty\Checkout\Api\FeeRepositoryInterface;
use Amasty\Checkout\Model\AdditionalFields;
use Amasty\Checkout\Model\AdditionalFieldsManagement;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\Delivery;
use Amasty\Checkout\Model\Gift\Messages;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\GiftMessage\Model\Message;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Additional Layout processor with all private and dynamic data
 *
 * @since 3.0.0
 */
class CustomerProcessor implements LayoutProcessorInterface
{
    /**
     * @var Messages
     */
    private $giftMessages;

    /**
     * @var Delivery
     */
    private $delivery;

    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * @var AdditionalFieldsManagement
     */
    private $additionalFieldsManagement;

    /**
     * @var Config
     */
    private $checkoutConfig;

    /**
     * @var LayoutWalker
     */
    private $walker;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LayoutWalkerFactory
     */
    private $walkerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(
        Messages $giftMessages,
        Delivery $delivery,
        Subscriber $subscriber,
        AdditionalFieldsManagement $additionalFieldsManagement,
        Config $checkoutConfig,
        LayoutWalkerFactory $walkerFactory,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        FeeRepositoryInterface $feeRepository,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {
        $this->giftMessages = $giftMessages;
        $this->delivery = $delivery;
        $this->subscriber = $subscriber;
        $this->additionalFieldsManagement = $additionalFieldsManagement;
        $this->checkoutConfig = $checkoutConfig;
        $this->walkerFactory = $walkerFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->feeRepository = $feeRepository;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->checkoutConfig->isEnabled()) {
            return $jsLayout;
        }
        $this->walker = $this->walkerFactory->create(['layoutArray' => $jsLayout]);

        $this->processAdditionalStepLayout();
        $this->processGiftLayout();

        if ($this->checkoutConfig->getDeliveryDateConfig('enabled')
            && !$this->checkoutSession->getQuote()->isVirtual()
        ) {
            $delivery = $this->delivery->findByQuoteId($this->checkoutSession->getQuoteId());

            $amcheckoutDelivery = [
                'date' => $delivery->getData('date'),
                'time' => $delivery->getData('time'),
                'comment' => $delivery->getData('comment'),
            ];
            $this->walker->setValue('components.checkoutProvider.amcheckoutDelivery', $amcheckoutDelivery);
        }

        return $this->walker->getResult();
    }

    /**
     * Additional fields in the Summary Block (Review Block)
     */
    protected function processAdditionalStepLayout()
    {
        $fieldsValue = $this->additionalFieldsManagement->getByQuoteId($this->checkoutSession->getQuoteId());
        $this->processNewsletterLayout($fieldsValue);

        if (!$this->checkoutConfig->getAdditionalOptions('comment')) {
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.comment');
        } elseif ($fieldsValue->getComment()) {
            $this->walker->setValue('{ADDITIONAL_STEP}.>>.comment.default', $fieldsValue->getComment());
        }

        if ($this->checkoutConfig->getAdditionalOptions('create_account') === '0'
            || $this->checkoutSession->getQuote()->getCustomer()->getId() !== null
        ) {
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.checkboxes.>>.register');
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.checkboxes.>>.date_of_birth');
        } else {
            if (!$this->checkoutConfig->canShowDob()) {
                $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.checkboxes.>>.date_of_birth');
            } elseif ($fieldsValue->getDateOfBirth()) {
                $this->walker->setValue(
                    '{ADDITIONAL_STEP}.>>.checkboxes.>>.date_of_birth.default',
                    $fieldsValue->getDateOfBirth()
                );
            }

            if ($this->checkoutConfig->getAdditionalOptions('create_account') === '1') {
                $registerChecked = (bool)$this->checkoutConfig->getAdditionalOptions('create_account_checked');
                if ($fieldsValue->getRegister() !== null) {
                    $registerChecked = (bool)$fieldsValue->getRegister();
                }

                $this->walker->setValue('{ADDITIONAL_STEP}.>>.checkboxes.>>.register.checked', $registerChecked);
                if ($registerChecked) {
                    $this->walker->setValue('{ADDITIONAL_STEP}.>>.checkboxes.>>.register.value', $registerChecked);
                }

                $fieldsValue->setRegister($registerChecked);
            } else {
                $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.checkboxes.>>.register');
                $registerChecked = true;
            }

            $this->walker->setValue('{ADDITIONAL_STEP}.>>.checkboxes.>>.date_of_birth.visible', $registerChecked);
        }

        $fieldsValue->save();
    }

    /**
     * Visibility and status if the subscribe checkbox
     *
     * @param AdditionalFields $fieldsValue
     */
    private function processNewsletterLayout($fieldsValue)
    {
        $newsletterConfig = (bool)$this->checkoutConfig->getAdditionalOptions('newsletter');

        if ($newsletterConfig && $this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            //TODO move to dynamic processor
            $this->subscriber->loadByCustomerId($customerId);
            $newsletterConfig = !$this->subscriber->isSubscribed();
        }

        if (!$newsletterConfig) {
            $this->walker->unsetByPath('{ADDITIONAL_STEP}.>>.checkboxes.>>.subscribe');
        } else {
            $subscribeCheck = (bool)$this->checkoutConfig->getAdditionalOptions('newsletter_checked');
            if ($fieldsValue->getSubscribe() !== null) {
                $subscribeCheck = (bool)$fieldsValue->getSubscribe();
            }
            $this->walker->setValue('{ADDITIONAL_STEP}.>>.checkboxes.>>.subscribe.checked', $subscribeCheck);
            if ($subscribeCheck) {
                $this->walker->setValue('{ADDITIONAL_STEP}.>>.checkboxes.>>.subscribe.value', $subscribeCheck);
            }

            $fieldsValue->setSubscribe($subscribeCheck);
        }
    }

    /**
     * Gift Wrap and Gift Messages processor
     */
    private function processGiftLayout()
    {
        if (!$this->checkoutConfig->isGiftWrapEnabled() || $this->checkoutConfig->isGiftWrapModuleEnabled()) {
            $this->walker->unsetByPath('{GIFT_WRAP}');
        } else {
            $amount = $this->checkoutConfig->getGiftWrapFee();

            $rate = $this->storeManager->getStore()->getBaseCurrency()->getRate(
                $this->storeManager->getStore()->getCurrentCurrency()
            );

            $amount *= $rate;

            $formattedPrice = $this->priceCurrency->format($amount, false);

            $this->walker->setValue('{GIFT_WRAP}.description', __('Gift wrap %1', $formattedPrice));
            $this->walker->setValue('{GIFT_WRAP}.fee', $amount);

            $fee = $this->feeRepository->getByQuoteId($this->checkoutSession->getQuoteId());

            if ($fee->getId()) {
                $this->walker->setValue('{GIFT_WRAP}.checked', true);
            }
        }

        if (empty($messages = $this->giftMessages->getGiftMessages())) {
            $this->walker->unsetByPath('{GIFT_MESSAGE_CONTAINER}');
        } else {
            $itemMessage = $quoteMessage = [
                'component' => 'uiComponent',
                'children' => [],
            ];
            $checked = false;

            /** @var Message $message */
            foreach ($messages as $key => $message) {
                if ($message->getId()) {
                    $checked = true;
                }

                $node = $message
                    ->setData('item_id', $key)
                    ->toArray(['item_id', 'sender', 'recipient', 'message', 'title']);

                $node['component'] = 'Amasty_Checkout/js/view/additional/gift-messages/message';
                if ($key) {
                    $itemMessage['children'][] = $node;
                } else {
                    $quoteMessage['children'][] = $node;
                }
            }
            $this->walker->setValue(
                '{GIFT_MESSAGE_CONTAINER}.config.popUpForm.options.messages',
                $this->translateTextForCheckout()
            );
            $this->walker->setValue('{GIFT_MESSAGE_CONTAINER}.>>.checkbox.checked', $checked);
            $this->walker->setValue('{GIFT_MESSAGE_CONTAINER}.>>.checkbox.checked', $checked);
            $this->walker->setValue('{GIFT_MESSAGE_CONTAINER}.>>.item_messages', $itemMessage);
            $this->walker->setValue('{GIFT_MESSAGE_CONTAINER}.>>.quote_message', $quoteMessage);
        }
    }

    /**
     * @return array
     */
    public function translateTextForCheckout(): array
    {
        $messages['gift'] = __('Gift messages has been successfully updated')->render();
        $messages['update'] = __('Update')->render();
        $messages['close'] = __('Close')->render();

        return $messages;
    }
}
