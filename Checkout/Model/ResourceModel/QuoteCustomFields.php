<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\ResourceModel;

use Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface;

/**
 * Class QuoteCustomFields
 */
class QuoteCustomFields extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'amasty_amcheckout_quote_custom_fields';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, QuoteCustomFieldsInterface::ID);
    }
}
