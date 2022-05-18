<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Framework\View\Asset;

class ConfigInterfacePlugin
{
    /**
     * @var \Amasty\Checkout\Model\Optimization\BundleService
     */
    private $bundleService;

    public function __construct(\Amasty\Checkout\Model\Optimization\BundleService $bundleService)
    {
        $this->bundleService = $bundleService;
    }

    /**
     * Force enable bundling for checkout
     *
     * @param \Magento\Framework\View\Asset\ConfigInterface $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterIsBundlingJsFiles(\Magento\Framework\View\Asset\ConfigInterface $subject, $result)
    {
        if (!$result && $this->bundleService->canLoadBundle()) {
            return true;
        }

        return $result;
    }
}
