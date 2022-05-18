<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin;

use Amasty\Checkout\Helper\Onepage;
use Amasty\Checkout\Model\Config;

class LayoutProcessor
{
    /**
     * @var array
     */
    protected $orderFixes = [];

    /**
     * @var Onepage
     */
    private $onepageHelper;

    /**
     * @var Config
     */
    private $checkoutConfig;

    public function __construct(
        Onepage $onepageHelper,
        Config $checkoutConfig
    ) {
        $this->onepageHelper = $onepageHelper;
        $this->checkoutConfig = $checkoutConfig;
    }

    /**
     * @param $field
     * @param $order
     */
    public function setOrder($field, $order)
    {
        $this->orderFixes[$field] = $order;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        if ($this->checkoutConfig->isEnabled()) {
            $layoutRoot = &$result['components']['checkout']['children']['steps']['children']['shipping-step']
                           ['children']['shippingAddress']['children'];
            $layoutRoot['customer-email']['component'] = 'Amasty_Checkout/js/view/form/element/email';

            foreach ($this->orderFixes as $code => $order) {
                $layoutRoot['shipping-address-fieldset']['children'][$code]['sortOrder'] = $order;
            }
        }

        return $result;
    }
}
