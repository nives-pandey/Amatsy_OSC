<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\Data\FeeInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Fee
 */
class Fee extends AbstractModel implements FeeInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\Checkout\Model\ResourceModel\Fee::class);
    }

    /**
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @return int
     */
    public function getBaseAmount()
    {
        return $this->getData(self::BASE_AMOUNT);
    }

    /**
     * @param int $id
     *
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setOrderId($id)
    {
        $this->setData(self::ORDER_ID, $id);

        return $this;
    }

    /**
     * @param int $id
     *
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setQuoteId($id)
    {
        $this->setData(self::QUOTE_ID, $id);

        return $this;
    }

    /**
     * @param int $amount
     *
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);

        return $this;
    }

    /**
     * @param int $amount
     *
     * @return \Amasty\Checkout\Api\Data\FeeInterface
     */
    public function setBaseAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);

        return $this;
    }
}
