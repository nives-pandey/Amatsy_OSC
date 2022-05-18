<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Amasty\Checkout\Model\ResourceModel\Delivery\CollectionFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class Delivery
 */
class Delivery extends AbstractModel
{
    /**
     * @var CollectionFactory
     */
    protected $deliveryCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $deliveryCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->deliveryCollectionFactory = $deliveryCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init(\Amasty\Checkout\Model\ResourceModel\Delivery::class);
    }

    public function findByQuoteId($quoteId)
    {
        $delivery = $this->findByField($quoteId, 'quote_id');

        if (!$delivery->getId()) {
            $delivery->setData('quote_id', $quoteId);
        }

        return $delivery;
    }

    public function findByOrderId($orderId)
    {
        return $this->findByField($orderId, 'order_id');
    }

    public function findByField($value, $field)
    {
        /** @var \Amasty\Checkout\Model\ResourceModel\Delivery\Collection $deliveryCollection */
        $deliveryCollection = $this->deliveryCollectionFactory->create();

        /** @var \Amasty\Checkout\Model\Delivery $delivery */
        $delivery = $deliveryCollection
            ->addFieldToFilter($field, $value)
            ->getFirstItem();

        return $delivery;
    }
}
