<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Quote\ResourceModel;

/**
 * Class Collection
 */
class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Collection
{
    /**
     * @return int|string
     */
    public function getSize()
    {
        return $this->getConnection()->fetchOne($this->getSelectCountSql(), $this->_bindParams);
    }
}
