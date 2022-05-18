<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\ResourceModel;

/**
 * Class Fee
 */
class Fee extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'amasty_amcheckout_additional_fee';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
