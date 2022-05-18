<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Field\Edit\Tabs;

use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Checkout\Block\Adminhtml\Field\Edit\Group;
use Amasty\Checkout\Block\Adminhtml\Field\Edit\Group\Row\Renderer as RowRenderer;
use Amasty\Checkout\Block\Adminhtml\Field\Edit\Group\Renderer as GroupRenderer;
use Amasty\Checkout\Model\FormManagement;

/**
 * Class AbstractTab
 */
class AbstractTab extends Generic implements TabInterface
{
    /**
     * @var Group
     */
    protected $groupRows;

    /**
     * @var FormManagement
     */
    protected $formManagement;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Group $groupRows,
        FormManagement $formManagement,
        array $data = []
    ) {
        $this->groupRows = $groupRows;
        $this->formManagement = $formManagement;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $layout = $this->getLayout();
        $nameInLayout = $this->getNameInLayout();

        $this->groupRows->setRowRenderer(
            $layout->createBlock(
                RowRenderer::class,
                $nameInLayout . '_row_element'
            )
        );

        $this->groupRows->setGroupRenderer(
            $layout->createBlock(
                GroupRenderer::class,
                $nameInLayout . '_group_element'
            )
        );

        return parent::_prepareLayout();
    }
}
