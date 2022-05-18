<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Observer\QuoteSubmit;

use Amasty\Base\Model\Serializer;
use Amasty\Checkout\Model\Gdpr\ConsentsProcessor;
use Amasty\Checkout\Model\ModuleEnable;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Observer\Checkout\ConsentRegistry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Event 'sales_order_place_after'
 */
class ProcessGdprConsents implements ObserverInterface
{
    /**
     * @var ConsentsProcessor
     */
    private $consentsProcessor;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    public function __construct(
        ConsentsProcessor $consentsProcessor,
        Serializer $serializer,
        ObjectManagerInterface $objectManager,
        ModuleEnable $moduleEnable
    ) {
        $this->consentsProcessor = $consentsProcessor;
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
        $this->moduleEnable = $moduleEnable;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        if (!$this->moduleEnable->isGdprEnable()) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $additionalInfo = $order->getPayment()->getAdditionalInformation();
        /** @var ConsentRegistry $consentRegistry */
        $consentRegistry = $this->objectManager->get(ConsentRegistry::class);
        $consentsData = $consentRegistry->getConsents();

        if (isset($additionalInfo[RegistryConstants::CONSENTS]) && empty($consentsData)) {
            $consentsData = $this->serializer->unserialize($additionalInfo[RegistryConstants::CONSENTS]);
        }

        if (!empty($consentsData)) {
            $this->consentsProcessor->process($order, $consentsData);
        }
    }
}
