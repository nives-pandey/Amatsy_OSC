<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Field\Edit;

use Amasty\Checkout\Block\Adminhtml\Renderer\Template;
use Magento\Backend\Block\Template\Context;
use Amasty\Checkout\Helper\Onepage;
use Amasty\Checkout\Model\ModuleEnable;

/**
 * Class AdditionalOptions
 */
class AdditionalOptions extends Template
{
    protected $_template = 'Amasty_Checkout::fields/edit/additional_options.phtml';

    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    public function __construct(
        Context $context,
        Onepage $helper,
        ModuleEnable $moduleEnable,
        array $data = []
    ) {
        $this->moduleEnable = $moduleEnable;
        parent::__construct($context, $helper, $data);
    }

    /**
     * @return bool
     */
    public function isOrderAttributesEnable()
    {
        return $this->moduleEnable->isOrderAttributesEnable();
    }

    /**
     * @return bool
     */
    public function isCustomerAttributesEnable()
    {
        return $this->moduleEnable->isCustomerAttributesEnable();
    }
}
