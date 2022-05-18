<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Optimization;

class BundleService implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const COLLECT_SCRIPT_PATH = 'Amasty_Checkout/js/action/create-js-bundle';

    /**
     * @var \Amasty\Checkout\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * Flag is bundle file loaded (available)
     *
     * @var bool
     */
    private $bundleLoaded = false;

    public function __construct(\Amasty\Checkout\Model\Config $config, \Magento\Framework\View\LayoutInterface $layout)
    {
        $this->config = $config;
        $this->layout = $layout;
    }

    /**
     * Is script which collecting bundle file can be initiated.
     *
     * @return bool
     */
    public function canCollectBundle()
    {
        return !$this->bundleLoaded && $this->isEnabled();
    }

    /**
     * @return bool
     */
    public function canLoadBundle()
    {
        return $this->isEnabled() && in_array('amasty_checkout', $this->layout->getUpdate()->getHandles());
    }

    public function setBundleLoaded()
    {
        $this->bundleLoaded = true;
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return $this->config->isEnabled() && $this->config->isJsBundleEnabled();
    }
}
