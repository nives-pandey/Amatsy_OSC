<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Observer\Admin;

use Amasty\Checkout\Block\Adminhtml\Sales\Order\Create\Deliverydate;
use Amasty\Checkout\Block\Adminhtml\Sales\Order\Delivery;
use Amasty\Checkout\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ViewInformation
 */
class ViewInformation implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $configProvider;

    public function __construct(
        Config $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $elementName = $observer->getElementName();
        $transport = $observer->getTransport();
        $html = $transport->getOutput();
        $block = $observer->getLayout()->getBlock($elementName);
        $blockName = null;
        $checkDeliveryEnable = false;
        $flagName = 'amcheckout_delivery_' . $elementName;

        switch ($elementName) {
            case 'order_info':
                $blockName = Delivery::class;
                break;
            case 'form_account':
                $blockName = Deliverydate::class;
                $checkDeliveryEnable = true;
                break;
        }

        if (empty($blockName)
            || ($checkDeliveryEnable && !$this->configProvider->getDeliveryDateConfig('enabled'))
            || $block->hasData($flagName)
        ) {
            return;
        }

        $deliveryBlock = $observer->getLayout()->createBlock($blockName);
        $html .= $deliveryBlock->toHtml();
        $block->setData($flagName, true);
        $transport->setOutput($html);
    }
}
