<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Color
 */
class Color extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var input = $("#' . $element->getHtmlId() . '");
                    input.css("backgroundColor", "' . $value . '");

                    input.ColorPicker({
                        color: "' . $value . '",
                        onChange: function (hsb, hex, rgb) {
                            input.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
            </script>';

        return $html;
    }
}
