<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

namespace Amasty\Checkout\Model\ResourceModel\Field;

/**
 * Class Store
 */
class Store extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'amasty_amcheckout_field_store';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
