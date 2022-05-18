<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Controller\Adminhtml\Reports;

use Magento\Backend\App\Action;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Checkout::reports');
        $resultPage->getConfig()->getTitle()->prepend(__('Checkout Analytics'));

        return $resultPage;
    }

    /**
     * Determine if authorized to perform group action.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Checkout::reports');
    }
}
