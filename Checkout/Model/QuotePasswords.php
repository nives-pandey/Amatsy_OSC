<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\Data\QuotePasswordsInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class QuotePasswords
 */
class QuotePasswords extends AbstractModel implements QuotePasswordsInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\Checkout\Model\ResourceModel\QuotePasswords::class);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId()
    {
        return $this->_getData(QuotePasswordsInterface::QUOTE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId($quoteId)
    {
        $this->setData(QuotePasswordsInterface::QUOTE_ID, $quoteId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash()
    {
        return $this->_getData(QuotePasswordsInterface::PASSWORD_HASH);
    }

    /**
     * @inheritdoc
     */
    public function setPasswordHash($passwordHash)
    {
        $this->setData(QuotePasswordsInterface::PASSWORD_HASH, $passwordHash);

        return $this;
    }
}
