<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * frontend_model for enable_agreements configuration field
 * set url for Terms and Conditions management
 */
class TermsAndConditions extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->setComment(
            __(
                "You can set Terms and Conditions "
                . "<a target='_blank' href='%1'>here</a>.",
                $this->getUrl("checkout/agreement/index")
            )
        );

        return parent::render($element);
    }
}
