<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Field;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Store
 */
class Store extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Amasty\Checkout\Model\ResourceModel\Field\Store::class);
    }
}
