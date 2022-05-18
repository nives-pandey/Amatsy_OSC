<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Field
 */
abstract class Field extends Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Checkout::checkout_settings_fields');
    }
}
