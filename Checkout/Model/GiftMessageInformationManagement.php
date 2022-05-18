<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\GiftMessageInformationManagementInterface;
use Magento\GiftMessage\Api\CartRepositoryInterface;
use Magento\GiftMessage\Api\ItemRepositoryInterface;
use Magento\GiftMessage\Model\MessageFactory;

/**
 * Class GiftMessageInformationManagement
 */
class GiftMessageInformationManagement implements GiftMessageInformationManagementInterface
{
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
        CartRepositoryInterface $cartRepository,
        ItemRepositoryInterface $itemRepository,
        MessageFactory $messageFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->itemRepository = $itemRepository;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @inheritdoc
     */
    public function update($cartId, $giftMessages)
    {
        foreach ($giftMessages as $messageData) {

            /** @var \Magento\GiftMessage\Model\Message $message */
            $message = $this->messageFactory->create();

            $message->setData([
                'message' => $messageData['message'],
                'sender' => $messageData['sender'],
                'recipient' => $messageData['recipient'],
            ]);

            try {
                if ($messageData['item_id'] == \Amasty\Checkout\Model\Gift\Messages::QUOTE_MESSAGE_INDEX) {
                    $this->cartRepository->save($cartId, $message);
                } else {
                    $this->itemRepository->save($cartId, $message, $messageData['item_id']);
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}
