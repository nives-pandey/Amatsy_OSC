<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Block\Template\Context;

/**
 * Class BillingAddress
 */
class BillingAddress extends Field
{
    /**
     * ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        Context $context,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
            $element->setDisabled(true);
            $element->setComment(
                __(
                    'Please update your Magento to version 2.2 or newer to make this setting available.'
                )
            );
        }

        return parent::render($element);
    }
}
