<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class QuoteCustomFields
 */
class QuoteCustomFields extends AbstractModel implements QuoteCustomFieldsInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\Checkout\Model\ResourceModel\QuoteCustomFields::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteId($id)
    {
        $this->setData(self::QUOTE_ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingValue()
    {
        return $this->getData(self::BILLING_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingValue($value)
    {
        $this->setData(self::BILLING_VALUE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingValue()
    {
        return $this->getData(self::SHIPPING_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingValue($value)
    {
        $this->setData(self::SHIPPING_VALUE, $value);

        return $this;
    }
}
