<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Braintree\Model;

/**
 * Restrict execution while disabled.
 * Braintree have a few payments with web API,
 * API request take a long time to finish (like 0.8 sec)
 * Braintre doesn't check is payment enabled, just send requests to API for each checkout load
 * which can take up to 4 sec.
 * This class used as VirtualType Plugin (frontend DI).
 * @since 3.0.0
 */
class AbstractConfigProviderPlugin
{
    /**
     * @var \Magento\Payment\Gateway\Config\Config
     */
    private $paymentConfig;

    public function __construct(\Magento\Payment\Gateway\Config\Config $paymentConfig)
    {
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * If payment disabled then restrict requesting web API in checkout configs.
     *
     * @param \Magento\Checkout\Model\ConfigProviderInterface $subject
     * @param callable $proceed
     *
     * @return array
     */
    public function aroundGetConfig(\Magento\Checkout\Model\ConfigProviderInterface $subject, callable $proceed)
    {
        if (!$this->isActive()) {
            return [];
        }

        return $proceed();
    }

    private function isActive(): bool
    {
        if (method_exists($this->paymentConfig, 'isActive')) {
            return (bool)$this->paymentConfig->isActive();
        }

        return (bool)$this->paymentConfig->getValue('active');
    }
}
