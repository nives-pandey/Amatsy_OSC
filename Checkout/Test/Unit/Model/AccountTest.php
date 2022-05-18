<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\Account;
use Amasty\Checkout\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AccountTest
 *
 * @see Account
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AccountTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ReflectionTrait;
    use Traits\ObjectManagerTrait;

    const ORDER_ID = 1;

    /**
     * @var object
     */
    private $model;

    /**
     * @var MockObject
     */
    private $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Address|MockObject
     */
    private $billing;

    /**
     * @var \Magento\Sales\Model\Order|MockObject
     */
    private $order;

    /**
     * @var MockObject
     */
    private $eventManager;

    /**
     * @var MockObject
     */
    private $timezone;

    /**
     * @var \Amasty\Checkout\Model\AdditionalFields
     */
    private $additionalFields;

    protected function setUp(): void
    {
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);

        $this->billing = $this->createPartialMock(\Magento\Sales\Model\Order\Address::class, []);

        $this->order = $this->createPartialMock(\Magento\Sales\Model\Order::class, ['getBillingAddress']);
        $this->order->setId(static::ORDER_ID);

        $orderRepository = $this->createMock(\Magento\Sales\Model\OrderRepository::class);
        $orderRepository->expects($this->any())->method('get')->willReturnReference($this->order);

        $customer = $this->createMock(\Magento\Customer\Model\Data\Customer::class);
        $orderCustomerService = $this->createMock(\Magento\Sales\Model\Order\CustomerManagement::class);
        $orderCustomerService->expects($this->any())->method('create')->willReturn($customer);

        $this->eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);

        $this->timezone = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);

        $this->additionalFields = $this->getObjectManager()->getObject(\Amasty\Checkout\Model\AdditionalFields::class);

        $this->model = $this->getObjectManager()->getObject(
            Account::class,
            [
                'customerSession' => $this->customerSession,
                'orderCustomerService' => $orderCustomerService,
                'timezone' => $this->timezone,
                'orderRepository' => $orderRepository,
                'eventManager' => $this->eventManager
            ]
        );
    }

    /**
     * @covers Account::create
     * @TODO rewrite test
     */
    public function testCreate()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->additionalFields->setDateOfBirth(true);

        $this->order->expects($this->once())->method('getBillingAddress')->willReturnReference($this->billing);
        $this->eventManager->expects($this->once())->method('dispatch');
        $this->timezone->expects($this->once())->method('date')->willReturnCallback(
            function () {
                return new \DateTime();
            }
        );

        $this->model->create(static::ORDER_ID, $this->additionalFields);
    }

    /**
     * @covers Account::create
     */
    public function testCreateCustomerIsLoggedIn()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);

        $this->order->expects($this->never())->method('getBillingAddress');
        $this->eventManager->expects($this->never())->method('dispatch');
        $this->timezone->expects($this->never())->method('date');

        $this->model->create(static::ORDER_ID, $this->additionalFields);
    }

    /**
     * @covers Account::create
     */
    public function testCreateNoOrder()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->order->setId(null);

        $this->order->expects($this->never())->method('getBillingAddress');
        $this->eventManager->expects($this->never())->method('dispatch');
        $this->timezone->expects($this->never())->method('date');

        $this->model->create(static::ORDER_ID, $this->additionalFields);
    }
}
