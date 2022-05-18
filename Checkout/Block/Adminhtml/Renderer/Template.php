<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Template\Context;
use Amasty\Checkout\Helper\Onepage;

/**
 * Class Template
 */
class Template extends \Magento\Backend\Block\Template implements RendererInterface
{

    /**
     * @var Onepage
     */
    protected $helper;

    public function __construct(
        Context $context,
        Onepage $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    public function isStoreSelected()
    {
        return $this->_request->getParam('store', false) !== false;
    }

    /**
     * @param null $moduleName
     * @return bool
     */
    public function isModuleExist($moduleName = null)
    {
        return $this->helper->isModuleOutputEnabled($moduleName);
    }
}
