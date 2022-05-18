<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Backend;

use Amasty\Checkout\Model\Config;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\Value;

/**
 * For deleting value in hidden field, when we use "inherit".
 * Layout config have 2 fields and only 1 inherit checkbox.
 * @since 3.0.0
 */
class LayoutBuilderSave extends Value implements ProcessorInterface
{
    /**
     * Process config value
     *
     * @param string $value Raw value of the configuration field
     * @return string Processed value
     */
    public function processValue($value)
    {
        return $value;
    }

    /**
     * Don't allow save is inherit box checked.
     *
     * @return bool
     */
    public function isSaveAllowed()
    {
        return parent::isSaveAllowed() && !$this->isInherit();
    }

    /**
     * Processing object after delete data
     *
     * @return $this
     */
    public function afterDelete()
    {
        if ($this->isInherit()) {
            $pathListToDelete = [
                Config::PATH_PREFIX . Config::LAYOUT_BUILDER_BLOCK . Config::FIELD_LAYOUT_BUILDER_CONFIG,
                Config::PATH_PREFIX . Config::DESIGN_BLOCK . Config::FIELD_CHECKOUT_LAYOUT_MODERN,
                Config::PATH_PREFIX . Config::DESIGN_BLOCK . Config::FIELD_CHECKOUT_LAYOUT,
                Config::PATH_PREFIX . Config::DESIGN_BLOCK . Config::FIELD_CHECKOUT_DESIGN,
            ];
            $this->_resourceCollection
                ->addFieldToFilter('path', $pathListToDelete)
                ->addFieldToFilter('scope_id', $this->getScopeId())
                ->addFieldToFilter('scope', $this->getScope())
                ->walk('delete');
        }

        return parent::afterDelete();
    }

    /**
     * Is "use parent config" checkbox checked.
     *
     * @return bool
     */
    private function isInherit()
    {
        $data = $this->getData('groups');

        return (bool)(int)$this->walkGet(
            [
                'design',
                'groups',
                'layout',
                'fields',
                Config::FIELD_FRONTEND_LAYOUT_CONFIG,
                'inherit'
            ],
            $data
        );
    }

    /**
     * @param array $path
     * @param array $haystack
     * @return mixed|null
     */
    private function walkGet(array $path, array $haystack)
    {
        $key = array_shift($path);

        $value = $haystack[$key] ?? null;

        if (count($path) && is_array($value)) {
            return $this->walkGet($path, $value);
        }

        return $value;
    }
}
