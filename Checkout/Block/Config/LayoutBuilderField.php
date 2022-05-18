<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Add new type of field renderer - hidden field with custom id
 * @method AbstractElement getElement()
 */
class LayoutBuilderField extends Field
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_template = 'Amasty_Checkout::system/config/form/field/layout_builder_field.phtml';
        parent::_construct();
    }

    /**
     * Get the grid and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_toHtml();
    }
}
