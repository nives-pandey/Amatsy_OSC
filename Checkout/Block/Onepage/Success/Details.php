<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Onepage\Success;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;

/**
 * Class Details
 */
class Details extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $session;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        if (!$this->registry->registry('current_order')) {
            $this->registry->register('current_order', $this->getOrder());
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->session->getLastRealOrder();
    }
}
