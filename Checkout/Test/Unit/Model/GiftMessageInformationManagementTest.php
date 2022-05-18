<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\GiftMessageInformationManagement;
use Amasty\Checkout\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class GiftMessageInformationManagementTest
 *
 * @see GiftMessageInformationManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class GiftMessageInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     *  @covers GiftMessageInformationManagementTest::update
     */
    public function testUpdate()
    {
        $data = [
            [
                'message' => 'test1',
                'sender' => 'test1',
                'recipient' => 'test1',
                'item_id' => 0
            ],
            [
                'message' => 'test1',
                'sender' => 'test1',
                'recipient' => 'test1',
                'item_id' => 1
            ]
        ];
        $messageFactory = $this->createMock(\Magento\GiftMessage\Model\MessageFactory::class);
        $cartRepository = $this->createMock(\Magento\GiftMessage\Api\CartRepositoryInterface::class);
        $itemRepository = $this->createMock(\Magento\GiftMessage\Api\ItemRepositoryInterface::class);
        $model = $this->getObjectManager()->getObject(
            GiftMessageInformationManagement::class,
            [
                'messageFactory' => $messageFactory,
                'cartRepository' => $cartRepository,
                'itemRepository' => $itemRepository,
            ]
        );
        $message = $this->getObjectManager()->getObject(\Magento\GiftMessage\Model\Message::class);

        $messageFactory->expects($this->any())->method('create')->willReturn($message);
        $cartRepository->expects($this->once())->method('save');
        $itemRepository->expects($this->once())->method('save');

        $this->assertTrue($model->update(1, []));
        $this->assertTrue($model->update(1, $data));
    }
}
