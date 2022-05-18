<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Field\Edit;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Factory;
use Amasty\Checkout\Block\Adminhtml\Field\Edit\Group\RowFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory;

use Magento\Framework\Escaper;

/**
 * Class Group
 */
class Group extends Fieldset
{
    /**
     * @var RendererInterface
     */
    protected static $_rowRenderer;

    /**
     * @var RendererInterface
     */
    protected static $_groupRenderer;
    
    /**
     * @var RowFactory
     */
    protected $rowFactory;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        RowFactory $rowFactory,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->rowFactory = $rowFactory;
    }

    /**
     * @param int $id
     * @param array $data
     * @param bool $after
     *
     * @return \Magento\Framework\Data\Form
     */
    public function addRow($id, $data, $after = false)
    {
        /** @var \Amasty\Checkout\Block\Adminhtml\Field\Edit\Group\Row $row */
        $row = $this->rowFactory->create(['data' => $data]);
        $row->setId($id);
        $element = $this->addElement($row, $after);

        if ($renderer = $this->getRowRenderer()) {
            $row->setRenderer($renderer);
        }

        return $element;
    }

    /**
     * @return RendererInterface
     */
    public function getRowRenderer()
    {
        return self::$_rowRenderer;
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRowRenderer(RendererInterface $renderer)
    {
        self::$_rowRenderer = $renderer;
    }

    /**
     * @return RendererInterface
     */
    public function getGroupRenderer()
    {
        return self::$_groupRenderer;
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setGroupRenderer(RendererInterface $renderer)
    {
        self::$_groupRenderer = $renderer;
    }
}
