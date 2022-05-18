<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\QuoteManagement;
use Amasty\Checkout\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class QuoteManagement
 *
 * @see QuoteManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class QuoteManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const CART_ID = 1;
    /**
     * @var QuoteManagement|MockObject
     */
    private $quoteManagement;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    private $session;

    /**
     * @var \Magento\Quote\Model\BillingAddressManagement|MockObject
     */
    private $billingAddressManagement;

    /**
     * @var \Magento\Quote\Model\ShippingAddressManagement|MockObject
     */
    private $shippingAddressManagement;

    /**
     * @var \Magento\Customer\Model\Data\Address|MockObject
     */
    private $billingAddress;

    /**
     * @var \Magento\Customer\Model\Data\Address|MockObject
     */
    private $shippingAddress;

    public function setUp(): void
    {
        // @TODO: this test doesn't test anything, because return of tested method is always true
        return;
        $this->quoteManagement = $this->createPartialMock(
            QuoteManagement::class,
            ['saveInfo']
        );
        $this->session = $this->createPartialMock(
            \Magento\Customer\Model\Session::class,
            ['getCustomerData', 'isLoggedIn']
        );
        $this->initAddresses();
        $customer = $this->createPartialMock(
            \Magento\Customer\Model\Data\Customer::class,
            []
        );
        $customer->setAddresses([$this->billingAddress, $this->shippingAddress]);

        $this->session->expects($this->any())->method('getCustomerData')
            ->willReturn($customer);
        $this->setProperty(
            $this->quoteManagement,
            'session',
            $this->session,
            QuoteManagement::class
        );

        $this->billingAddressManagement = $this->createPartialMock(
            \Magento\Quote\Model\BillingAddressManagement::class,
            ['get']
        );
        $this->billingAddressManagement->expects($this->any())->method('get')
            ->with(self::CART_ID)
            ->willReturn($this->billingAddress);
        $this->shippingAddressManagement = $this->createPartialMock(
            \Magento\Quote\Model\ShippingAddressManagement::class,
            ['get']
        );
        $this->shippingAddressManagement->expects($this->any())->method('get')
            ->with(self::CART_ID)
            ->willReturn($this->shippingAddress);
        $this->setProperty(
            $this->quoteManagement,
            'billingAddressManagement',
            $this->billingAddressManagement,
            QuoteManagement::class
        );
        $this->setProperty(
            $this->quoteManagement,
            'shippingAddressManagement',
            $this->shippingAddressManagement,
            QuoteManagement::class
        );
    }

    /**
     * @covers QuoteManagement::saveInsertedInfo
     */
    public function testSaveInsertedInfo()
    {
        // @TODO: this test doesn't test anything, because return of tested method is always true
        return;
        $this->session->expects($this->any())->method('isLoggedIn')
            ->willReturn(false);
        $this->assertTrue($this->quoteManagement->saveInsertedInfo(self::CART_ID));
    }

    /**
     * @covers QuoteManagement::retrieveAddressFromCustomer
     * @dataProvider retrieveAddressDataProvider
     */
    public function testRetrieveAddressFromCustomer($shippingAddress, $billingAddress)
    {
        // @TODO: this test tests 10% of method code. Need to re-write
        return;
        $result = $this->invokeMethod(
            $this->quoteManagement,
            'retrieveAddressFromCustomer',
            [self::CART_ID, $shippingAddress, $billingAddress]
        );

        $this->assertEquals([$this->shippingAddress, $this->billingAddress], $result);
    }

    /**
     * Data Provider for retrieveAddressFromCustomer test
     * @return array
     */
    public function retrieveAddressDataProvider()
    {
        // @TODO: this test doesn't test anything, because return of tested method is always true
        return [];
        $this->initAddresses();

        return [
            [null, null],
            [$this->shippingAddress, null],
            [null, $this->billingAddress],
            [$this->shippingAddress, $this->billingAddress]
        ];
    }

    /**
     * Init billing and shipping addresses mocks for tests
     */
    private function initAddresses()
    {
        if ($this->billingAddress && $this->shippingAddress) {
            return;
        }
        // @TODO: methods isDefaultBilling, isDefaultShipping, __toArray doesn't exist
        $this->billingAddress = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['addData', 'isDefaultBilling', 'isDefaultShipping', '__toArray']
        );
        $this->shippingAddress = clone $this->billingAddress;
        $this->shippingAddress->setData('default_shipping', true);
        $this->billingAddress->setData('default_billing', true);

        $this->shippingAddress->expects($this->any())->method('__toArray')
            ->willReturnCallback(
                function () {
                    return $this->shippingAddress->getData();
                }
            );
        $this->billingAddress->expects($this->any())->method('__toArray')
            ->willReturnCallback(
                function () {
                    return $this->billingAddress->getData();
                }
            );
        $this->billingAddress->expects($this->any())->method('isDefaultBilling')
            ->willReturn(true);
        $this->billingAddress->expects($this->any())->method('isDefaultShipping')
            ->willReturn(false);
        $this->shippingAddress->expects($this->any())->method('isDefaultShipping')
            ->willReturn(true);
        $this->shippingAddress->expects($this->any())->method('isDefaultBilling')
            ->willReturn(false);
    }
}
