<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\View\Page\Config;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;
use Amasty\Checkout\Model\Config as ConfigProvider;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\GroupedCollection;

class Renderer
{
    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var GroupedCollection
     */
    private $pageAssets;

    /**
     * @var ConfigProvider
     */
    private $checkoutConfig;

    public function __construct(
        ConfigProvider $checkoutConfig,
        Repository $assetRepo,
        GroupedCollection $pageAssets
    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->assetRepo = $assetRepo;
        $this->pageAssets = $pageAssets;
    }

    /**
     * Disable Amasty OSC js mixins if module is disabled
     *
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     *
     * @return array
     */
    public function beforeRenderAssets(MagentoRenderer $subject, $resultGroups = [])
    {
        if (!$this->checkoutConfig->isEnabled()) {
            $file = 'Amasty_Checkout::js/amastyCheckoutDisabled.js';
            $asset = $this->assetRepo->createAsset($file);
            $this->pageAssets->insert($file, $asset, 'requirejs/require.js');
            return [$resultGroups];
        }

        return [$resultGroups];
    }
}
