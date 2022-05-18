<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Observer\QuoteSubmit;

use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Amasty\Checkout\Api\AdditionalFieldsManagementInterface;
use Amasty\Checkout\Model\Subscription;
use Amasty\Checkout\Model\FeeRepository;
use Amasty\Checkout\Model\Delivery;
use Amasty\Checkout\Model\ResourceModel\Delivery as DeliveryResource;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\CollectionFactory;
use Amasty\Checkout\Model\OrderCustomFieldsFactory;
use Amasty\Checkout\Model\ResourceModel\OrderCustomFields;
use Magento\Quote\Api\Data\CartInterface;

/**
 * event sales_model_service_quote_submit_success
 */
class AfterSubmitObserver implements ObserverInterface
{
    /**
     * @var AdditionalFieldsManagementInterface
     */
    private $fieldsManagement;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var FeeRepository
     */
    private $feeRepository;

    /**
     * @var Delivery
     */
    private $delivery;

    /**
     * @var DeliveryResource
     */
    private $deliveryResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var OrderCustomFieldsFactory
     */
    private $orderCustomFieldsFactory;

    /**
     * @var OrderCustomFields
     */
    private $orderCustomFieldsResource;

    public function __construct(
        AdditionalFieldsManagementInterface $fieldsManagement,
        Subscription $subscription,
        FeeRepository $feeRepository,
        Delivery $delivery,
        DeliveryResource $deliveryResource,
        Config $config,
        CollectionFactory $quoteCollectionFactory,
        OrderCustomFieldsFactory $orderCustomFieldsFactory,
        OrderCustomFields $orderCustomFieldsResource
    ) {
        $this->fieldsManagement = $fieldsManagement;
        $this->subscription = $subscription;
        $this->feeRepository = $feeRepository;
        $this->delivery = $delivery;
        $this->deliveryResource = $deliveryResource;
        $this->config = $config;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->orderCustomFieldsFactory = $orderCustomFieldsFactory;
        $this->orderCustomFieldsResource = $orderCustomFieldsResource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$order) {
            return $this;
        }

        $orderId = (int)$order->getId();
        $quoteId = (int)$quote->getId();

        $fee = $this->feeRepository->getByQuoteId($quoteId);
        if ($fee->getId()) {
            $fee->setOrderId($orderId);
            $this->feeRepository->save($fee);
        }

        $delivery = $this->delivery->findByQuoteId($quoteId);

        if ($delivery->getId()) {
            $delivery->setData('order_id', $orderId);
            $this->deliveryResource->save($delivery);
        }

        $fields = $this->fieldsManagement->getByQuoteId($quoteId);

        $this->convertCustomFields($quote, $orderId);

        if (!$fields->getId()) {
            return $this;
        }

        if ($fields->getSubscribe()) {
            $this->subscription->subscribe($order->getCustomerEmail());
        }

        return $this;
    }

    /**
     * Convert Custom Fields from Quote to Order
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $orderId
     */
    private function convertCustomFields(CartInterface $quote, int $orderId): void
    {
        $shipping = $quote->getShippingAddress();
        $billing = $quote->getBillingAddress();

        foreach (CustomFieldsConfigInterface::CUSTOM_FIELDS_ARRAY as $attributeCode) {
            /** @var \Amasty\Checkout\Model\OrderCustomFields $orderCustomField */
            $orderCustomField = $this->orderCustomFieldsFactory->create(
                ['data' => ['name' => $attributeCode, 'order_id' => $orderId]]
            );
            $orderCustomField->setDataChanges(false);

            $attribute = $shipping->getCustomAttribute($attributeCode);
            if ($attribute) {
                $orderCustomField->setShippingValue($attribute->getValue());
            }

            $attribute = $billing->getCustomAttribute($attributeCode);
            if ($attribute) {
                $orderCustomField->setBillingValue($attribute->getValue());
            }

            if ($orderCustomField->hasDataChanges()) {
                $this->orderCustomFieldsResource->save($orderCustomField);
            }
        }
    }
}
