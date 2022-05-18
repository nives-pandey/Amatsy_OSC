<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Config;

use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\Config\CheckoutBlocksProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

/**
 * Change container template of config field to custom template
 * @method AbstractElement getElement()
 */
class LayoutBuilderArea extends Field
{
    /**
     * @var CheckoutBlocksProvider
     */
    private $checkoutBlocksProvider;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        CheckoutBlocksProvider $checkoutBlocksProvider,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutBlocksProvider = $checkoutBlocksProvider;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_template = 'Amasty_Checkout::system/config/form/field/layout_builder_area.phtml';

        parent::_construct();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->_decorateRowHtml($element, $this->_toHtml());
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    public function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);
        $colspan = "3";

        if ($isCheckboxRequired) {
            $colspan = "4";
        }
        $html = '<td colspan="' . $colspan . '">' . $html . '</td>';
        return parent::_decorateRowHtml($element, $html);
    }

    /**
     * @return array
     */
    public function getBlockDefaultNames()
    {
        return $this->checkoutBlocksProvider->getDefaultBlockTitles();
    }

    /**
     * @return array
     */
    public function getConfigForUseDefault()
    {
        $scope = $this->getElement()->getScope();
        $scopeId = $this->getElement()->getScopeId();
        list($parentScope, $parentScopeId) = $this->getParentScopeAndScopeId($scope, $scopeId);
        $design = $this->scopeConfig->getValue(
            Config::PATH_PREFIX
                . Config::DESIGN_BLOCK
                . Config::FIELD_CHECKOUT_DESIGN,
            $parentScope,
            $parentScopeId
        );

        $layoutField = Config::FIELD_CHECKOUT_LAYOUT;
        if ($design == "1") {
            $layoutField = Config::FIELD_CHECKOUT_LAYOUT_MODERN;
        }

        $layout = $this->scopeConfig->getValue(
            Config::PATH_PREFIX
                . Config::DESIGN_BLOCK
                . $layoutField,
            $parentScope,
            $parentScopeId
        );

        return [
            'design' => (int)$design,
            'layout' => $layout,
        ];
    }

    /**
     * @param string $scope
     * @param int $scopeId
     * @return array
     */
    private function getParentScopeAndScopeId($scope, $scopeId)
    {
        $parentScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $parentScopeId = 0;
        if ($scope == ScopeInterface::SCOPE_STORES) {
            $parentScope = ScopeInterface::SCOPE_WEBSITE;
            $parentScopeId = $this->_storeManager->getStore($scopeId)->getWebsiteId();
        }

        return [$parentScope, $parentScopeId];
    }
}
