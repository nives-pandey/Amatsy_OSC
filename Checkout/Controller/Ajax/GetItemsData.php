<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Controller\Ajax;

use Amasty\Checkout\Helper\Item;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class GetItemsData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Item
     */
    private $itemHelper;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var ItemResolverInterface|null
     */
    private $itemResolver;

    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Item $itemHelper,
        Image $imageHelper
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->itemHelper = $itemHelper;
        $this->imageHelper = $imageHelper;
        if (interface_exists(ItemResolverInterface::class)) {
            $this->itemResolver = $this->_objectManager->get(ItemResolverInterface::class);
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $response */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $layout = $resultPage->getLayout();
        $layout->getUpdate()->addHandle(['amasty_checkout_prototypes']);

        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        $optionsData = [];
        $imageData = [];
        /** @var QuoteItem $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $optionsData[$item->getId()] = $this->itemHelper->getItemOptionsConfig($quote, $item, $layout);
            $imageData[$item->getId()] = $this->getItemImageData($item);
        }

        $jsonResponse = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResponse->setData([
            'image_data' => $imageData,
            'options_data' => $optionsData,
        ]);

        return $jsonResponse;
    }

    /**
     * @param QuoteItem $item
     * @return array
     */
    private function getItemImageData(QuoteItem $item)
    {
        $product = $item->getProduct();

        if ($this->itemResolver !== null) {
            $product = $this->itemResolver->getFinalProduct($item);
        }

        $imageHelper = $this->imageHelper->init($product, 'mini_cart_product_thumbnail');

        return [
            'src' => $imageHelper->getUrl(),
            'width' => $imageHelper->getWidth(),
            'height' => $imageHelper->getHeight(),
            'alt' => $item->getName()
        ];
    }
}
