<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Field\Edit\Group\Row;

use Amasty\Checkout\Block\Adminhtml\Renderer\Template;

/**
 * Class Renderer
 */
class Renderer extends Template
{
    protected $_template = 'Amasty_Checkout::widget/form/renderer/row.phtml';

    /**
     * @param int $attributeId
     *
     * @return string
     */
    public function getOrderAttrUrl($attributeId)
    {
        return parent::getUrl('amorderattr/attribute/edit', ['attribute_id' => $attributeId]);
    }

    /**
     * @param int $attributeId
     *
     * @return string
     */
    public function getCustomerAttrUrl($attributeId)
    {
        return parent::getUrl('amcustomerattr/attribute/edit', ['attribute_id' => $attributeId]);
    }
}
