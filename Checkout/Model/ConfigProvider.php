<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Model\Config\CheckoutBlocksProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Phrase;

/**
 * Add checkout blocks config to checkout config
 * @since 3.0.0
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CONFIG_KEY = 'checkoutBlocksConfig';

    /**
     * @var CheckoutBlocksProvider
     */
    private $checkoutBlocksProvider;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(CheckoutBlocksProvider $checkoutBlocksProvider, Config $configProvider)
    {
        $this->checkoutBlocksProvider = $checkoutBlocksProvider;
        $this->configProvider = $configProvider;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            static::CONFIG_KEY => $this->getCheckoutBlocksConfig()
        ];
    }

    /**
     * @return array
     */
    private function getCheckoutBlocksConfig()
    {
        $blocksConfig = $this->configProvider->getCheckoutBlocksConfig();
        foreach ($blocksConfig as &$column) {
            foreach ($column as &$block) {
                if (empty($block['title'])) {
                    $block['title'] = $this->getDefaultTitle($block['name']);
                }
            }
        }

        return $blocksConfig;
    }

    /**
     * @param string $blockName
     * @return Phrase|string
     */
    private function getDefaultTitle($blockName)
    {
        $defaultTitles = $this->checkoutBlocksProvider->getDefaultBlockTitles();

        return $defaultTitles[$blockName] ?? "";
    }
}
