<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\ResourceModel\AdditionalFields;

/**
 * Class Collection
 *
 * @method \Amasty\Checkout\Model\AdditionalFields[] getItems()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\Checkout\Model\AdditionalFields::class,
            \Amasty\Checkout\Model\ResourceModel\AdditionalFields::class
        );
    }
}
