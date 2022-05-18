<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Observer\Admin\QuoteSubmit;

use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Amasty\Checkout\Api\Data\OrderCustomFieldsInterface;
use Amasty\Checkout\Api\DeliveryInformationManagementInterface;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\QuoteCustomFieldsFactory;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields as QuoteCustomFieldsResource;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BeforeAdminSubmitObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var QuoteCustomFieldsResource
     */
    private $quoteCustomFieldsResource;

    /**
     * @var QuoteCustomFieldsFactory
     */
    private $quoteCustomFieldsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DeliveryInformationManagement
     */
    private $deliveryInformationManagement;

    public function __construct(
        Config $config,
        QuoteCustomFieldsResource $quoteCustomFieldsResource,
        QuoteCustomFieldsFactory $quoteCustomFieldsFactory,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        DeliveryInformationManagementInterface $deliveryInformationManagement
    ) {
        $this->config = $config;
        $this->quoteCustomFieldsResource = $quoteCustomFieldsResource;
        $this->quoteCustomFieldsFactory = $quoteCustomFieldsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->deliveryInformationManagement = $deliveryInformationManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $countOfCustomFields = CustomFieldsConfigInterface::COUNT_OF_CUSTOM_FIELDS;
        $index = CustomFieldsConfigInterface::CUSTOM_FIELD_INDEX;

        for ($index; $index <= $countOfCustomFields; $index++) {
            $customFieldIndex = 'custom_field_' . $index;
            $customAttributes = $this->request->getParam('order');

            if (!$data = $this->getCustomFieldData($customAttributes, $customFieldIndex)) {
                continue;
            }

            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getEvent()->getQuote();
            /** @var \Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\Collection $customFieldsCollection */
            $customFieldsCollection = $this->collectionFactory->create();
            /** @var \Amasty\Checkout\Model\QuoteCustomFields $quoteCustomField */
            $quoteCustomField = $this->quoteCustomFieldsFactory->create();
            $customFieldsCollection->addFilterByQuoteIdAndCustomField($quote->getId(), $customFieldIndex);

            if ($customFieldsCollection->getSize()) {
                $quoteCustomField = $customFieldsCollection->getFirstItem();
            }

            $data['name'] = $customFieldIndex;
            $data['quote_id'] = $quote->getId();
            $quoteCustomField->addData($data);
            $this->quoteCustomFieldsResource->save($quoteCustomField);
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $quoteId = $quote->getId();
        $deliveryInfo = $this->request->getParam('am_checkout_deliverydate');

        if (!empty($deliveryInfo) && is_array($deliveryInfo)) {
            $deliveryInfoDefault = [
                'date' => null,
                'time' => null,
                'comment' => null
            ];
            $deliveryInfo += $deliveryInfoDefault;
            $this->deliveryInformationManagement->update(
                $quoteId,
                $deliveryInfo['date'],
                $deliveryInfo['time'],
                $deliveryInfo['comment']
            );
        }
    }

    /**
     * @param array $customAttributes
     * @param string $customFieldIndex
     *
     * @return array
     */
    private function getCustomFieldData($customAttributes, $customFieldIndex)
    {
        $data = [];

        if (empty($customAttributes) || !is_array($customAttributes)) {
            return $data;
        }

        foreach ($customAttributes as $key => $value) {
            if (is_array($value) && array_key_exists($customFieldIndex, $value)) {
                switch ($key) {
                    case 'billing_address':
                        $data[OrderCustomFieldsInterface::BILLING_VALUE] = $value[$customFieldIndex];
                    // no break;
                    case 'shipping_address':
                        $data[OrderCustomFieldsInterface::SHIPPING_VALUE] = $value[$customFieldIndex];
                        break;
                }
            }
        }

        return $data;
    }
}
