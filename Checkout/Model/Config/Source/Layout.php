<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Amasty\Checkout\Helper\Onepage;

/**
 * Class Layout
 */
class Layout implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => Onepage::TWO_COLUMNS, 'label' => '2 Columns'],
            ['value' => Onepage::THREE_COLUMNS, 'label' => '3 Columns'],
        ];
    }
}
