<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Field;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;

/**
 * Class Edit
 */
class Edit extends FormContainer
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_field';
        $this->_blockGroup = 'Amasty_Checkout';

        parent::_construct();

        $this->buttonList->remove('reset');
    }
}
