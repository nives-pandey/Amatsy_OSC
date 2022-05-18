<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Reports;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class RefreshButton
 */
class RefreshButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        $url = $this->context->getUrl('amasty_checkout/reports/index');

        return [
            'label' => __('Refresh'),
            'class' => 'refresh primary',
            'on_click' => "submitRefresh('$url')",
        ];
    }
}
