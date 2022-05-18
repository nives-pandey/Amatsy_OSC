<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Onepage;

use Magento\Framework\View\Element\Template;
use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Amasty\Checkout\Model\Field;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\Color;

/**
 * Additional Dynamical (have configuration) Styles and Scripts for checkout page
 */
class Head extends Template
{
    /**
     * @var Field
     */
    protected $fieldSingleton;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Color
     */
    private $color;

    public function __construct(
        Template\Context $context,
        Field $fieldSingleton,
        Config $configProvider,
        Color $color,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->fieldSingleton = $fieldSingleton;
        $this->configProvider = $configProvider;
        $this->color = $color;
    }

    public function getFields()
    {
        $result = [];

        /** @var \Amasty\Checkout\Model\Field $field */
        foreach ($this->fieldSingleton->getConfig($this->_storeManager->getStore()->getId()) as $field) {
            $result[$field->getData('attribute_code')] = $field->getData('width');
            if ($field->getData('attribute_code') === 'region') {
                $result['region_id'] = $field->getData('width');
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getCustomFont()
    {
        return $this->escapeHtml(strtok(trim($this->configProvider->getCustomFont()), ':'));
    }

    /**
     * @return bool|string
     */
    public function getHeadingTextColor()
    {
        return $this->color->getHeadingTextColor();
    }

    /**
     * @return bool|string
     */
    public function getSummaryBackgroundColor()
    {
        return $this->color->getSummaryBackgroundColor();
    }

    /**
     * @return bool|string
     */
    public function getBackgroundColor()
    {
        return $this->color->getBackgroundColor();
    }

    /**
     * @return array|bool
     * @throws \Less_Exception_Compiler
     */
    public function getButtonColor()
    {
        return $this->color->getButtonColor();
    }

    /**
     * @return Config
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }
}
