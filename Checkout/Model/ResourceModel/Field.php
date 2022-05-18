<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Field
 */
class Field extends AbstractDb
{
    const MAIN_TABLE = 'amasty_amcheckout_field';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

    /**
     * @inheritdoc
     */
    public function deleteField(AbstractModel $field)
    {
        return parent::delete($field);
    }
}
