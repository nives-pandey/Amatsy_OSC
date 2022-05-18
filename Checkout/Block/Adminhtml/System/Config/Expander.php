<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;

/**
 * Block Extender For Expand Sections
 */
class Expander extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Checkout::system/config/form/expander.phtml';

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->getRequest()->getParam('section');
    }

    /**
     * @return string
     */
    public function getExpand()
    {
        return $this->getRequest()->getParam('expand');
    }
}
