<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DisplayAgreements
 */
class DisplayAgreements implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'payment_method', 'label' => __('Below the Selected Payment Method')],
            ['value' => 'order_totals', 'label' => __('Below the Order Total')]
        ];
    }
}
