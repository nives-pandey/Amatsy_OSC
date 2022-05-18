<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\ResourceModel;

/**
 * Class Delivery
 */
class Delivery extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'amasty_amcheckout_delivery';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
