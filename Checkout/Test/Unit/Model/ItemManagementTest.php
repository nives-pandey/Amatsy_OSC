<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\ItemManagement;
use Amasty\Checkout\Test\Unit\Traits;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

/**
 * Class ItemManagementTest
 *
 * @see ItemManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ItemManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var ItemManagement
     */
    private $model;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterface
     */
    private $address;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var Uri
     */
    private $zendUri;

    /**
     * @var array
     */
    private $mockAddressData = [
        'firstName' => [
            'method' => 'getFirstname',
            'sampleData' => 'John'
        ],
        'lastName' => [
            'method' => 'getLastname',
            'sampleData' => 'Doe'
        ],
        'company' => [
            'method' => 'getCompany',
            'sampleData' => 'Magento'
        ],
        'address' => [
            'method' => 'getStreetLine1',
            'sampleData' => '11501 Domain Dr'
        ],
        'city' => [
            'method' => 'getCity',
            'sampleData' => 'Austin'
        ],
        'state' => [
            'method' => 'getRegionCode',
            'sampleData' => 'TX'
        ],
        'zip' => [
            'method' => 'getPostcode',
            'sampleData' => '78758'
        ],
        'country' => [
            'method' => 'getCountryId',
            'sampleData' => 'US'
        ],
    ];

    protected function setUp(): void
    {
        $this->address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->cartRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->cart = $this->createMock(\Magento\Checkout\Model\Cart::class);
        $this->zendUri = $this->getObjectManager()->getObject(\Zend\Uri\Uri::class);

        $this->model = $this->getObjectManager()->getObject(
            ItemManagement::class,
            [
                'cartRepository' => $this->cartRepository,
                'cart' => $this->cart,
                'zendUri' => $this->zendUri
            ]
        );
    }

    /**
     * @covers ItemManagement::remove
     */
    public function testRemove()
    {
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteItem = $this->createMock(\Magento\Quote\Model\Quote\Item::class);

        $this->cartRepository->expects($this->any())->method('get')->willReturn($quote);
        $this->cartRepository->expects($this->once())->method('save');
        $quote->expects($this->any())->method('isVirtual')->will($this->onConsecutiveCalls(true, true , false, true));
        $quote->expects($this->any())->method('getItemById')->willReturn($quoteItem);
        $quoteItem->expects($this->any())->method('getId')->will($this->onConsecutiveCalls(1, 0));

        $this->model->remove(1, 2, $this->address);
        $this->assertFalse($this->model->remove(1, 2, $this->address));
    }

    /**
     * @covers ItemManagement::update
     */
    public function testUpdateWithoutItem()
    {
        $this->expectException(LocalizedException::class);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);

        $this->cartRepository->expects($this->any())->method('get')->willReturn($quote);
        $this->cart->expects($this->any())->method('getQuote')->willReturn($quote);
        $shippingAddress = $this->createAddressMock('shipping');
        $billingAddress = $this->createAddressMock('billing');
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddress);
        $quote->expects($this->any())->method('getBillingAddress')->willReturn($billingAddress);
        $quote->expects($this->any())->method('isVirtual')->will($this->onConsecutiveCalls(true, true , false, true));
        $quote->expects($this->any())->method('getItemById')->willReturn(false);

        $this->model->update(1, 2, '');
    }

    /**
     * @param $prefix
     *
     * @return MockObject
     */
    private function createAddressMock($prefix)
    {
        $addressAdapterMock = $this->createMock(AddressAdapterInterface::class);

        foreach ($this->mockAddressData as $field) {
            $addressAdapterMock->method($field['method'])
                ->willReturn($prefix . $field['sampleData']);
        }

        return $addressAdapterMock;
    }

    /**
     * @covers ItemManagement::update
     */
    public function testUpdateWithStringItem()
    {
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('test');

        $this->cartRepository->expects($this->any())->method('get')->willReturn($quote);
        $this->cart->expects($this->any())->method('getQuote')->willReturn($quote);
        $this->cart->expects($this->any())->method('updateItem')->willReturn('test');
        $shippingAddress = $this->createAddressMock('shipping');
        $billingAddress = $this->createAddressMock('billing');
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddress);
        $quote->expects($this->any())->method('getBillingAddress')->willReturn($billingAddress);
        $quote->expects($this->any())->method('isVirtual')->will($this->onConsecutiveCalls(true, true , false, true));
        $quote->expects($this->any())->method('getItemById')->willReturn($quote);

        $this->model->update(1, 2, '');
    }

    /**
     * @covers ItemManagement::update
     */
    public function testUpdateWithErrorInItem()
    {
        $quote = $this->createpartialMock(
            \Magento\Quote\Model\Quote::class,
            ['isVirtual', 'getItemById', 'getShippingAddress', 'getBillingAddress']
        );

        $itemMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, []);
        $itemMock->setData('has_error', true);

        $this->expectException(LocalizedException::class);

        $this->cartRepository->expects($this->any())->method('get')->willReturn($quote);
        $this->cart->expects($this->any())->method('getQuote')->willReturn($quote);
        $shippingAddress = $this->createAddressMock('shipping');
        $billingAddress = $this->createAddressMock('billing');
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddress);
        $quote->expects($this->any())->method('getBillingAddress')->willReturn($billingAddress);
        $this->cart->expects($this->any())->method('updateItem')->willReturn($itemMock);
        $quote->expects($this->any())->method('isVirtual')->will($this->onConsecutiveCalls(true, true , false, true));

        $this->model->update(1, 2, '');
    }

    /**
     * @covers ItemManagement::update
     */
    public function testUpdate()
    {
        $quote = $this->createpartialMock(
            \Magento\Quote\Model\Quote::class,
            [
                'isVirtual',
                'getItemById',
                'getAllVisibleItems',
                'getShippingAddress',
                'getBillingAddress',
                'getItems'
            ]
        );

        $this->cartRepository->expects($this->any())->method('get')->willReturn($quote);
        $this->cart->expects($this->any())->method('getQuote')->willReturn($quote);
        $this->cart->expects($this->any())->method('updateItem')->willReturn($quote);
        $this->cart->expects($this->exactly(2))->method('save');
        $shippingAddress = $this->createAddressMock('shipping');
        $billingAddress = $this->createAddressMock('billing');
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddress);
        $quote->expects($this->any())->method('getBillingAddress')->willReturn($billingAddress);
        $quote->expects($this->any())->method('isVirtual')->will($this->onConsecutiveCalls(false, true));
        $quote->expects($this->any())->method('getItemById')->willReturn($quote);
        $quote->expects($this->any())->method('getAllVisibleItems')->willReturn([]);
        $quote->expects($this->any())->method('getItems')->willReturn([0 => []]);

        $this->assertFalse($this->model->update(1, 2, ''));
        $this->model->update(1, 2, '');
    }

    /**
     * @covers ItemManagement::parseStr
     */
    public function testParseStr()
    {
        $this->assertEquals(['test1' => '2', 'test2' => '3'], $this->model->parseStr('test1=2&test2=3'));
    }

    /**
     * @covers ItemManagement::prepareParams
     */
    public function testPrepareParams()
    {
        $this->assertEquals(
            ['id' => 1, 'options' => []],
            $this->invokeMethod($this->model, 'prepareParams', [['options' => []], 1])
        );

        $this->assertEquals(
            ['id' => 1, 'options' => []],
            $this->invokeMethod($this->model, 'prepareParams', [[], 1])
        );

        $this->assertEquals(
            ['id' => 1, 'options' => [], 'qty' => 2, 'reset_count' => true],
            $this->invokeMethod($this->model, 'prepareParams', [['qty' => 2], 1])
        );
    }
}
