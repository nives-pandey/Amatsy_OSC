<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

namespace Amasty\Checkout\Controller\Adminhtml\Field;

use Amasty\Checkout\Controller\Adminhtml\Field as FieldAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class Index extends FieldAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Checkout::checkout_settings_fields');
        $resultPage->addBreadcrumb(__('System'), __('System'));
        $resultPage->addBreadcrumb(__('One Step Checkout'), __('One Step Checkout'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Checkout Fields'));

        return $resultPage;
    }
}
