<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Module\Manager;
use Amasty\Base\Helper\Module;

/**
 * Frontend Model for option amasty_checkout/gift_wrap/enable
 */
class GiftWrapEnable extends Field
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var Module
     */
    private $moduleHelper;

    public function __construct(
        Context $context,
        Manager $moduleManager,
        Module $moduleHelper,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->moduleManager->isEnabled('Amasty_GiftWrap')) {
            return parent::_getElementHtml($element);
        }

        return '<div class="control-value">Not Installed</div>';
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _renderScopeLabel(AbstractElement $element)
    {
        if (!$this->moduleManager->isEnabled('Amasty_GiftWrap')) {
            return '';
        }

        return parent::_renderScopeLabel($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _renderValue(AbstractElement $element)
    {
        $link = 'https://amasty.com/gift-wrap-for-magento-2.html' .
            '?utm_source=extension&utm_medium=backend&utm_campaign=ext_list';

        //TODO Add Link To Gift Wrap Magento 2 Marketplace
        /*if ($this->moduleHelper->isOriginMarketplace()) {
            $link = 'https://marketplace.magento.com/amasty-gift-wrap.html';
        }*/
        $htmlLink = '<a href="' . $link . '">extension</a>';
        if ($this->moduleHelper->isOriginMarketplace()) {
            $htmlLink = "extension";
        }
        $element->setComment(
            __('Extended gift wrapping is available in Amasty Gift Wrap %1', $htmlLink)
        );

        if ($this->moduleManager->isEnabled('Amasty_GiftWrap')) {
            $urlParams['section'] = 'amgiftwrap';
            if ($this->getRequest()->getParam('store')) {
                $urlParams['store'] = $this->getRequest()->getParam('store');
            }
            $giftWrapConfigLink = $this->getUrl('adminhtml/system_config/edit', $urlParams);
            $element->setComment(
                __('Current setting duplicates the ‘Enabled’ setting from Gift Wrap plugin.') .
                __('Set ‘Yes’ to use functionality of <a href="%1">Gift Wrap</a> extension.', $giftWrapConfigLink) .
                __('If set to ‘No’, Gift Wrap will be disabled as well.')
            );
        }

        return parent::_renderValue($element);
    }
}
