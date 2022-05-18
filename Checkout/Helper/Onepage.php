<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Helper;

use Amasty\Checkout\Model\ResourceModel\Region\CollectionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data;
use Amasty\Checkout\Model\Config;

class Onepage extends AbstractHelper
{
    const ONE_COLUMN = '1column';
    const TWO_COLUMNS = '2columns';
    const THREE_COLUMNS = '3columns';

    /**
     * @var CollectionFactory
     */
    protected $regionsFactory;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        Context $context,
        CollectionFactory $regionsFactory,
        Data $jsonHelper,
        Config $configProvider,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
        $this->regionsFactory = $regionsFactory;
        $this->jsonHelper = $jsonHelper;
        $this->configProvider = $configProvider;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->configProvider->getTitle();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->configProvider->getDescription();
    }

    /**
     * @return bool
     */
    public function isAddressSuggestionEnabled()
    {
        return $this->configProvider->isAddressSuggestionEnabled();
    }

    /**
     * @return string
     */
    public function getGoogleMapsKey()
    {
        return $this->configProvider->getGoogleMapsKey();
    }

    /**
     * @return string
     */
    public function getRegionsJson()
    {
        return $this->jsonHelper->jsonEncode($this->getRegions());
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        /** @var \Amasty\Checkout\Model\ResourceModel\Region\Collection $collection */
        $collection = $this->regionsFactory->create();

        return $collection->fetchRegions();
    }

    /**
     * @return bool
     */
    public function isModernCheckoutDesign()
    {
        return (bool)$this->configProvider->getCheckoutDesign();
    }

    /**
     * @return string
     */
    public function getLayoutTemplate()
    {
        if ($this->isModernCheckoutDesign()) {
            return $this->configProvider->getLayoutModernTemplate();
        }

        return $this->configProvider->getLayoutTemplate();
    }

    /**
     * @return string
     */
    public function getDesignLayout()
    {
        if (!$this->checkoutSession->getQuote()->isVirtual()) {
            return $this->getLayoutTemplate();
        }

        return self::TWO_COLUMNS;
    }
}
