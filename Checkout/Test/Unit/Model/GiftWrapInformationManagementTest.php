<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\GiftWrapInformationManagement;
use Amasty\Checkout\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class GiftWrapInformationManagementTest
 *
 * @see GiftWrapInformationManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class GiftWrapInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers GiftWrapInformationManagement::update
     */
    public function testUpdate()
    {
        $cartRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $feeRepository = $this->createMock(\Amasty\Checkout\Api\FeeRepositoryInterface::class);
        $cartTotalRepository = $this->createMock(\Magento\Quote\Api\CartTotalRepositoryInterface::class);
        $feeFactory = $this->createMock(\Amasty\Checkout\Model\FeeFactory::class);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getBaseCurrency', 'getCurrentCurrency']);
        $store->expects($this->any())->method('getBaseCurrency')->willReturn($store);
        $store->expects($this->any())->method('getCurrentCurrency')->willReturn(1);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $model = $this->getObjectManager()->getObject(
            GiftWrapInformationManagement::class,
            [
                'cartRepository' => $cartRepository,
                'feeRepository' => $feeRepository,
                'cartTotalRepository' => $cartTotalRepository,
                'storeManager' => $storeManager,
                'feeFactory' => $feeFactory,
            ]
        );

        $quote = $this->getObjectManager()->getObject(\Magento\Quote\Model\Quote::class);
        $fee = $this->getObjectManager()->getObject(\Amasty\Checkout\Model\Fee::class);

        $cartTotalRepository->expects($this->any())->method('get');
        $feeRepository->expects($this->once())->method('delete');
        $feeRepository->expects($this->once())->method('save');
        $cartRepository->expects($this->any())->method('get')->willReturn($quote);
        $feeRepository->expects($this->any())->method('getByQuoteId')->willReturn($fee);
        $feeFactory->expects($this->any())->method('create')->willReturn($fee);

        $quote->setTotalsCollectedFlag(1);
        $model->update(1, true);

        $fee->setId(1);
        $model->update(1, false);
    }
}
