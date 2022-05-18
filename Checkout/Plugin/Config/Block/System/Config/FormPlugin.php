<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Config\Block\System\Config;

use Amasty\Checkout\Block\Adminhtml\System\Config\Expander;
use Magento\Config\Block\System\Config\Form;

class FormPlugin
{
    /**
     * @param Form $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(Form $subject, $result)
    {
        if ($subject->getRequest()->getParam('expand')) {
            $layout = $subject->getLayout();
            $blockExpander = $layout->createBlock(Expander::class);
            $result = $result . $blockExpander->toHtml();
        }

        return $result;
    }
}
