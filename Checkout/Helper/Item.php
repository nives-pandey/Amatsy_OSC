<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Item extends AbstractHelper
{
    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->registry = $registry;
    }

    /**
     * @param Quote $quote
     * @param QuoteItem|int $item
     * @param LayoutInterface $layout
     * @return array
     */
    public function getItemOptionsConfig(Quote $quote, $item, $layout)
    {
        /** @var \Magento\Catalog\Block\Product\View\Options $optionsBlock */
        $optionsBlock = $layout->getBlock('amcheckout.options.prototype');

        $quoteItem = is_object($item) ? $item : $quote->getItemById($item);

        $additionalConfig = [
            'isEditable' => true
        ];

        $product = $quoteItem->getProduct();

        $product->setPreconfiguredValues(
            $product->processBuyRequest($quoteItem->getBuyRequest())
        );

        // Fix issue in vendor/magento/module-tax/Observer/GetPriceConfigurationObserver.php
        $oldRegistryProduct = $this->registry->registry('current_product');

        if ($oldRegistryProduct) {
            $this->registry->unregister('current_product');
        }

        $this->registry->register('current_product', $product);

        if ($quoteItem->getData('product_type') == 'configurable') {
            $additionalConfig['configurableAttributes'] = $this->getConfigurableAttributesConfig(
                $quoteItem,
                $product,
                $layout
            );
        }

        if ($quoteItem->getProduct()->getOptions()) {
            $optionsBlock->setProduct($product);

            $customOptionsConfig = [
                'template' => $optionsBlock->toHtml(),
                'optionConfig' => $optionsBlock->getJsonConfig()
            ];

            $additionalConfig['customOptions'] = $customOptionsConfig;
        }

        if ($quoteItem->getProductType() == DownloadableType::TYPE_DOWNLOADABLE) {
            $additionalConfig['customOptions'] = $this->getDownloadableCustomOptionsConfig($quoteItem, $layout);
        }

        if ($quoteItem->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $additionalConfig['customOptions'] = $this->getBundleCustomOptionsConfig($quoteItem, $layout);
        }

        if ($quoteItem->getProductType() == 'giftcard') {
            $additionalConfig['customOptions'] = $this->getGiftCardCustomOptionsConfig($quoteItem, $layout);
        }

        $this->registry->unregister('current_product');
        if ($oldRegistryProduct) {
            $this->registry->register('current_product', $oldRegistryProduct);
        }

        return $additionalConfig;
    }

    /**
     * @param QuoteItem $quoteItem
     * @param \Magento\Catalog\Model\Product $product
     * @param LayoutInterface $layout
     *
     * @return array
     */
    private function getConfigurableAttributesConfig(QuoteItem $quoteItem, $product, $layout)
    {
        $buyRequest = $quoteItem->getBuyRequest();

        /** @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $configurableAttributesBlock */
        $configurableAttributesBlock = $layout->getBlock('amcheckout.super.prototype');

        $configurableAttributesBlock->unsetData('allow_products');
        $configurableAttributesBlock->addData([
            'product' => $product,
            'quote_item' => $quoteItem
        ]);

        $configurableAttributesConfig = [
            'selectedAttributes' => $buyRequest['super_attribute'],
            'template' => $configurableAttributesBlock->toHtml(),
            'spConfig' => $configurableAttributesBlock->getJsonConfig(),
        ];

        return $configurableAttributesConfig;
    }

    /**
     * @param QuoteItem $quoteItem
     * @param LayoutInterface $layout
     *
     * @return array
     */
    private function getDownloadableCustomOptionsConfig(QuoteItem $quoteItem, $layout)
    {
        /** @var \Magento\Downloadable\Block\Checkout\Cart\Item\Renderer $downloadableBlock */
        $downloadableBlock = $layout->getBlock('amcheckout.downloadable.prototype');
        $downloadableBlock->setItem($quoteItem);

        $customOptionsConfig = [
            'template' => $downloadableBlock->toHtml(),
            'optionConfig' => null
        ];

        return $customOptionsConfig;
    }

    /**
     * @param QuoteItem $quoteItem
     * @param LayoutInterface $layout
     *
     * @return array
     */
    private function getBundleCustomOptionsConfig(QuoteItem $quoteItem, $layout)
    {
        /** @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $bundleBlock */
        $bundleBlock = $layout->getBlock('amcheckout.bundle.prototype');
        $bundleBlock->setProduct($quoteItem->getProduct());
        $bundleBlock->setItem($quoteItem);
        $bundleBlock->getOptions(true);

        $customOptionsConfig = [
            'template' => $bundleBlock->toHtml(),
            'optionConfig' => $bundleBlock->getJsonConfig()
        ];

        return $customOptionsConfig;
    }

    /**
     * @param QuoteItem $quoteItem
     * @param LayoutInterface $layout
     *
     * @return array
     */
    private function getGiftCardCustomOptionsConfig(QuoteItem $quoteItem, $layout)
    {
        if (!$giftCardBlock = $layout->getBlock('amcheckout.giftcard.prototype')) {
            $giftCardBlock = $layout->createBlock(
                \Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard::class,
                'amcheckout.giftcard.prototype'
            );
        }

        $giftCardBlock->setTemplate('Amasty_Checkout::product/view/type/options/giftcard.phtml');
        $giftCardBlock->setItem($quoteItem);
        $giftCardBlock->setProduct($quoteItem->getProduct());

        $customOptionsConfig = [
            'template' => $giftCardBlock->toHtml(),
            'optionConfig' => null
        ];

        return $customOptionsConfig;
    }
}
