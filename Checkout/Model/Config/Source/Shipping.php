<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Source;

use Magento\Shipping\Model\Config\Source\Allmethods;

/**
 * Class Shipping
 */
class Shipping extends Allmethods
{
    /**
     * @inheritdoc
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $options = parent::toOptionArray(true);

        $options[0]['label'] = ' ';

        foreach ($options as &$option) {
            if (is_array($option['value'])) {
                foreach ($option['value'] as &$method) {
                    $method['label'] = preg_replace('#^\[.+?\]\s#', '', $method['label']);
                }
            }
        }

        return $options;
    }
}
