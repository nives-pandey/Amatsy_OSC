<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Observer\QuoteSubmit;

use Magento\Framework\Event\ObserverInterface;
use Amasty\Checkout\Api\AdditionalFieldsManagementInterface;
use Amasty\Checkout\Model\Config;
use Magento\Framework\Event\Observer;

/**
 * Class BeforeSubmitObserver
 */
class BeforeSubmitObserver implements ObserverInterface
{
    /**
     * @var AdditionalFieldsManagementInterface
     */
    private $fieldsManagement;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        AdditionalFieldsManagementInterface $fieldsManagement,
        Config $config
    ) {
        $this->fieldsManagement = $fieldsManagement;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isEnabled()) {
            return;
        }
        /** @var \Magento\Quote\Model\Quote $order */
        $quote = $observer->getEvent()->getQuote();
        $fields = $this->fieldsManagement->getByQuoteId($quote->getId());
        if ($fields->getComment()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            $history = $order->addStatusHistoryComment($fields->getComment());
            $history->setIsVisibleOnFront(true);
            $history->setIsCustomerNotified(true);
        }
    }
}
