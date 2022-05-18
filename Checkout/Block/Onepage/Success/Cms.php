<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Onepage\Success;

use Magento\Cms\Block\Block;
use Magento\Framework\View\Element\Context;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\BlockFactory;
use Amasty\Checkout\Model\Config;

/**
 * Class Cms
 */
class Cms extends Block
{
    /**
     * @var int
     */
    private $blockId;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Config $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $filterProvider, $storeManager, $blockFactory, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return int|string
     */
    public function getBlockId()
    {
        if ($this->blockId === null) {
            $this->blockId = $this->configProvider->getSuccessCustomBlockId();
        }

        return $this->blockId;
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeyInfo()
    {
        return array_merge(parent::getCacheKeyInfo(), ['store' . $this->_storeManager->getStore()->getId()]);
    }
}
