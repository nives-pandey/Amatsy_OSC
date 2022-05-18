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

/**
 * Add Link For Gift Wrap Option manage_fields_link_content
 */
class GiftWrapLink extends Field
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Context $context,
        Manager $moduleManager,
        array $data = []
    ) {
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
        if (!$this->moduleManager->isEnabled('Amasty_GiftWrap')) {
            return '';
        }
        $urlParams['section'] = 'amgiftwrap';
        if ($this->getRequest()->getParam('store')) {
            $urlParams['store'] = $this->getRequest()->getParam('store');
        }
        $element->setHref($this->getUrl('adminhtml/system_config/edit', $urlParams));
        $confirmMessage = $this->escapeQuote($this->escapeHtml(__('Unsaved changes will be discarded.')));
        $element->setOnclick('return confirm(\'' . $confirmMessage . '\')');

        return parent::_getElementHtml($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function _isInheritCheckboxRequired(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function _renderInheritCheckbox(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '';
    }
}
