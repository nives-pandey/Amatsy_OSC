<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Sales\Order\Email;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

/**
 * Class Comments
 */
class Comments extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(Template\Context $context, Registry $coreRegistry, array $data = [])
    {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('Amasty_Checkout::onepage/details/comments.phtml')
            ->setData('area', 'frontend');
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        if (!$this->hasData('order_entity')) {
            $order = $this->coreRegistry->registry('current_order');

            if (!$order && $this->getParentBlock()) {
                $order = $this->getParentBlock()->getOrder();
            }

            $this->setData('order_entity', $order);
        }

        return $this->getData('order_entity');
    }
}
