<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Gift;

use Magento\Checkout\Model\Session;
use Magento\GiftMessage\Helper\Message;
use Magento\Store\Model\StoreManagerInterface;
use Magento\GiftMessage\Model\ResourceModel\Message\CollectionFactory;
use Magento\GiftMessage\Api\CartRepositoryInterface;
use Magento\GiftMessage\Api\ItemRepositoryInterface;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\Quote\Model\Quote;

/**
 * Class Messages
 */
class Messages
{
    const QUOTE_MESSAGE_INDEX = 0;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Message
     */
    protected $messageHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    public function __construct(
        Session $checkoutSession,
        Message $messageHelper,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        CartRepositoryInterface $cartRepository,
        ItemRepositoryInterface $itemRepository,
        MessageFactory $messageFactory
    ) {

        $this->checkoutSession = $checkoutSession;
        $this->messageHelper = $messageHelper;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->cartRepository = $cartRepository;
        $this->itemRepository = $itemRepository;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGiftMessages()
    {
        $quote = $this->checkoutSession->getQuote();

        if ($quote->isVirtual()) {
            return false;
        }

        if (0 == $quote->getItemsCount()) {
            return false;
        }

        $messages = $this->getMessages($quote);

        /** @var \Magento\GiftMessage\Model\ResourceModel\Message\Collection $messageCollection */
        $messageCollection = $this->collectionFactory->create();
        $messageCollection->addFieldToFilter('gift_message_id', ['in' => $messages]);

        foreach ($messages as $i => $id) {
            $message = $messageCollection->getItemById($id);
            if (!$message) {
                $message = new \Magento\Framework\DataObject(['item_id' => $id]);
            }

            if ($i != self::QUOTE_MESSAGE_INDEX) {
                $for = $quote->getItemById($i)->getName();
            } else {
                $for = __('Whole Order');
            }

            $title = __('Gift Message for %1 (optional)', $for);
            $message->setData('title', $title);
            $messages[$i] = $message;
        }

        return $messages;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMessages(Quote $quote)
    {
        $messages = [];
        $store = $this->storeManager->getStore();

        if ($this->messageHelper->isMessagesAllowed('quote', $quote, $store)) {
            $messages[self::QUOTE_MESSAGE_INDEX] = $quote->getGiftMessageId();
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getIsVirtual()) {
                continue;
            }

            if (!$this->messageHelper->isMessagesAllowed('order_item', $quote, $store)) {
                continue;
            }

            $messages[$item->getId()] = $item->getGiftMessageId();
        }

        return $messages;
    }
}
