<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Field\Edit\Tabs;

use Magento\Store\Model\ScopeInterface;
use Amasty\Checkout\Model\Field;
use Amasty\Checkout\Api\Data\ManageCheckoutTabsInterface;
use Amasty\Checkout\Block\Adminhtml\Field\Edit\Tabs\AbstractTab;

/**
 * Class PaymentMethod
 */
class PaymentMethod extends AbstractTab
{
    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Payment Method');
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $storeId = $this->_request->getParam(ScopeInterface::SCOPE_STORE, Field::DEFAULT_STORE_ID);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formManagement->prepareForm(ManageCheckoutTabsInterface::PAYMENT_METHOD_TAB, $storeId);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
