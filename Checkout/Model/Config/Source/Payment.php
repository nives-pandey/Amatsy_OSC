<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Source;

use Magento\Payment\Model\Config\Source\Allmethods;

/**
 * Class Payment
 */
class Payment extends Allmethods
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        array_unshift($options, ['value' => '', 'label' => ' ']);

        foreach ($options as $key => $option) {
            if (!isset($options[$key]['value'])) {
                $options[$key]['value'] = null;
            }
        }

        return $options;
    }
}
