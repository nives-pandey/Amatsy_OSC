<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\ResourceModel\OrderCustomFields;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Checkout\Api\Data\OrderCustomFieldsInterface;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Checkout\Model\OrderCustomFields::class,
            \Amasty\Checkout\Model\ResourceModel\OrderCustomFields::class
        );
    }

    /**
     * @param int $orderId
     *
     * @return Collection
     */
    public function addFieldByOrderId($orderId)
    {
        return $this->addFieldToFilter(OrderCustomFieldsInterface::ORDER_ID, $orderId);
    }

    /**
     * @param int $orderId
     * @param string $customFieldIndex
     *
     * @return Collection
     */
    public function addFieldByOrderIdAndCustomField($orderId, $customFieldIndex)
    {
        return $this->addFieldByOrderId($orderId)
            ->addFieldToFilter(OrderCustomFieldsInterface::NAME, $customFieldIndex);
    }
}
