<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Model\Gdpr;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class ConsentsProcessor
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     * @param array $consentsData
     */
    public function process(OrderInterface $order, array $consentsData): void
    {
        $storeId = (int)$order->getStoreId();
        $customerId = (int)$order->getCustomerId();
        $email = (string)$order->getCustomerEmail();
        $consentsData = $this->groupConsentsData($consentsData);

        try {
            foreach ($consentsData as $from => $consentCodes) {
                $this->eventManager->dispatch(
                    'amasty_gdpr_consent_accept',
                    [
                        RegistryConstants::CONSENTS => $consentCodes,
                        RegistryConstants::CONSENT_FROM => $from,
                        RegistryConstants::CUSTOMER_ID => $customerId,
                        RegistryConstants::STORE_ID => $storeId,
                        RegistryConstants::EMAIL => $email
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param array $consentsData
     * @return array
     */
    private function groupConsentsData(array $consentsData): array
    {
        $grouped = [];

        foreach ($consentsData as $consentCode => $consent) {
            $from = $consent['from'] ?? ConsentLogger::FROM_CHECKOUT;
            $checked = $consent['checked'] ?? $consent;
            $grouped[$from][$consentCode] = $checked;
        }

        return $grouped;
    }
}
