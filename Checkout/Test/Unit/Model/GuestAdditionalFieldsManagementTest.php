<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\GuestAdditionalFieldsManagement;
use Amasty\Checkout\Test\Unit\Traits;

/**
 * Class GuestAdditionalFieldsManagement
 *
 * @see GuestAdditionalFieldsManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class GuestAdditionalFieldsManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const QUOTE_ID = 1;

    /**
     * @covers GuestAdditionalFieldsManagement::save
     */
    public function testSave()
    {
        $cartId = 1;
        $fields = $this->createMock(\Amasty\Checkout\Model\AdditionalFields::class);
        $guestFieldsManagment = $this->createPartialMock(GuestAdditionalFieldsManagement::class, []);

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

        $fieldsManagement = $this->createPartialMock(
            \Amasty\Checkout\Model\AdditionalFieldsManagement::class,
            ['save']
        );
        $fieldsManagement->expects($this->any())->method('save')
            ->with(self::QUOTE_ID, $fields)
            ->willReturn(true);

        $this->setProperty(
            $guestFieldsManagment,
            'quoteIdMaskFactory',
            $quoteIdMaskFactory,
            GuestAdditionalFieldsManagement::class
        );
        $this->setProperty(
            $guestFieldsManagment,
            'fieldsManagement',
            $fieldsManagement,
            GuestAdditionalFieldsManagement::class
        );

        $this->assertTrue($guestFieldsManagment->save($cartId, $fields));
    }
}
