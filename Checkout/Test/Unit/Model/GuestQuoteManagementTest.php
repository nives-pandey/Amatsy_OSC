<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\GuestQuoteManagement;
use Amasty\Checkout\Test\Unit\Traits;

/**
 * Class GuestQuoteManagement
 *
 * @see GuestQuoteManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class GuestQuoteManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const QUOTE_ID = 1;

    /**
     * @covers GuestQuoteManagement::saveInsertedInfo
     */
    public function testSaveInsertedInfo()
    {
        $cartId = 1;
        $guestQuoteManagement = $this->createPartialMock(GuestQuoteManagement::class, []);

        $quoteMaskId = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMask::class,
            ['load']
        );
        $quoteMaskId->expects($this->any())->method('load')->with($cartId, 'masked_id')
            ->willReturn($quoteMaskId);
        $quoteMaskId->setQuoteId(self::QUOTE_ID);

        $quoteIdMaskFactory = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMaskFactory::class,
            ['create']
        );
        $quoteIdMaskFactory->expects($this->any())->method('create')
            ->willReturn($quoteMaskId);

        $quoteManagement = $this->createMock(\Amasty\Checkout\Model\QuoteManagement::class);
        $quoteManagement->expects($this->any())->method('saveInsertedInfo')
            ->with(self::QUOTE_ID)
            ->willReturn(true);

        $this->setProperty(
            $guestQuoteManagement,
            'quoteIdMaskFactory',
            $quoteIdMaskFactory,
            GuestQuoteManagement::class
        );
        $this->setProperty(
            $guestQuoteManagement,
            'quoteManagement',
            $quoteManagement,
            GuestQuoteManagement::class
        );

        $this->assertTrue($guestQuoteManagement->saveInsertedInfo($cartId));
    }
}
