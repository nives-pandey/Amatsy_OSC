<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\Data\AdditionalFieldsInterface;

/**
 * Class AdditionalFields
 *
 * @method \Amasty\Checkout\Model\ResourceModel\AdditionalFields getResource()
 * @method \Amasty\Checkout\Model\ResourceModel\AdditionalFields\Collection getCollection()
 */
class AdditionalFields extends \Magento\Framework\Model\AbstractModel implements AdditionalFieldsInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\Checkout\Model\ResourceModel\AdditionalFields::class);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId()
    {
        return $this->_getData(AdditionalFieldsInterface::QUOTE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId($quoteId)
    {
        $this->setData(AdditionalFieldsInterface::QUOTE_ID, $quoteId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->_getData(AdditionalFieldsInterface::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->setData(AdditionalFieldsInterface::COMMENT, $comment);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubscribe()
    {
        return $this->_getData(AdditionalFieldsInterface::IS_SUBSCRIBE);
    }

    /**
     * @inheritdoc
     */
    public function setSubscribe($isSubscribe)
    {
        $this->setData(AdditionalFieldsInterface::IS_SUBSCRIBE, $isSubscribe);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegister()
    {
        return $this->_getData(AdditionalFieldsInterface::IS_REGISTER);
    }

    /**
     * @inheritdoc
     */
    public function setRegister($isRegister)
    {
        $this->setData(AdditionalFieldsInterface::IS_REGISTER, $isRegister);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateOfBirth()
    {
        return $this->_getData(AdditionalFieldsInterface::REGISTER_DOB);
    }

    /**
     * @inheritdoc
     */
    public function setDateOfBirth($registerDob)
    {
        $this->setData(AdditionalFieldsInterface::REGISTER_DOB, $registerDob);

        return $this;
    }
}
