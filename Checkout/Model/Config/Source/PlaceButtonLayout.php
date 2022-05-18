<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PlaceButtonLayout
 */
class PlaceButtonLayout implements OptionSourceInterface
{
    const PAYMENT = 'payment';
    const SUMMARY = 'summary';
    const FIXED_TOP = 'top';
    const FIXED_BOTTOM = 'bottom';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PAYMENT, 'label' => __('Below the Selected Payment Method')],
            ['value' => self::SUMMARY, 'label' => __('Below the Order Total')]
        ];
    }
}
