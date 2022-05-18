<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\Subscription;
use Amasty\Checkout\Test\Unit\Traits;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class Subscription
 *
 * @see Subscription
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class SubscriptionTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const CUSTOMER_EMAIL = 'test@test.com';

    const WEBSITE_ID = 1;

    const CUSTOMER_ID = 1;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subscriber;

    /**
     * @covers Subscription::subscribe
     * @dataProvider dataProvider
     */
    public function testSubscribe($email, $isGuest, $hasSubscriptionInDb, $expectedEmail)
    {
        $messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);
        if ($isGuest) {
            $messageManager->expects($this->once())->method('addSuccessMessage');
        } else {
            $messageManager->expects($this->never())->method('addSuccessMessage');
        }
        $messageManager->expects($this->never())->method('addExceptionMessage');

        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            []
        );
        $quote->setData('customer_email', self::CUSTOMER_EMAIL);

        $checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $checkoutSession->expects($this->any())->method('getQuote')
            ->willReturn($quote);

        $emailValidator = $this->createMock(\Magento\Framework\Validator\EmailAddress::class);
        $emailValidator->expects($this->any())->method('isValid')
            ->with(self::CUSTOMER_EMAIL)
            ->willReturn(true);

        $configProvider = $this->createMock(\Amasty\Checkout\Model\Config::class);
        $configProvider->expects($this->any())->method('allowGuestSubscribe')
            ->willReturn($isGuest);

        $customerDataObject = $this->createPartialMock(\Magento\Customer\Model\Data\Customer::class, []);

        $customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $customerSession->expects($this->any())->method('isLoggedIn')
            ->willReturn(!$isGuest);
        $customerSession->expects($this->any())->method('getCustomerDataObject')
            ->willReturn($customerDataObject);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);

        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getStore')
            ->willReturn($store);

        $customerAccountManagement = $this->createMock(\Magento\Customer\Model\AccountManagement::class);
        $customerAccountManagement->expects($this->any())->method('isEmailAvailable')
            ->with(self::CUSTOMER_EMAIL, self::WEBSITE_ID)
            ->willReturn($expectedEmail !== null);

        $this->subscriber = $this->createPartialMock(
            \Magento\Newsletter\Model\Subscriber::class,
            ['loadByEmail', 'subscribe']
        );

        if ($hasSubscriptionInDb) {
            $this->subscriber->expects($this->any())->method('loadByEmail')
                ->with(self::CUSTOMER_EMAIL)
                ->willReturnCallback(
                    function ($email) {
                        $this->subscriber->setId(self::CUSTOMER_ID);
                        $this->subscriber->setEmail($email);
                        $this->subscriber->setStatus(Subscriber::STATUS_UNSUBSCRIBED);

                        return $this->subscriber;
                    }
                );
        } else {
            $this->subscriber->expects($this->any())->method('loadByEmail')
                ->with(self::CUSTOMER_EMAIL)
                ->willReturnCallback(
                    function ($email) {
                        $this->subscriber->setEmail($email);

                        return $this->subscriber;
                    }
                );
        }
        $this->subscriber->expects($this->any())->method('subscribe')
            ->with(self::CUSTOMER_EMAIL)
            ->willReturn(\Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE);

        $subscriberFactory = $this->createMock(\Magento\Newsletter\Model\SubscriberFactory::class);
        $subscriberFactory->expects($this->any())->method('create')
            ->willReturn($this->subscriber);

        $subscription = $this->getMockBuilder(Subscription::class)
            ->setConstructorArgs(
                [
                    'subscriberFactory' => $subscriberFactory,
                    'messageManager' => $messageManager,
                    'checkoutSession' => $checkoutSession,
                    'emailValidator' => $emailValidator,
                    'configProvider' => $configProvider,
                    'customerSession' => $customerSession,
                    'storeManager' => $storeManager,
                    'customerAccountManagement' => $customerAccountManagement,
                ]
            )->getMockForAbstractClass();
        $subscription->subscribe($email);
        $this->assertEquals($expectedEmail, $this->subscriber->getEmail());
    }

    /**
     * Data provider for subscribe test
     * @return array
     */
    public function dataProvider()
    {
        return [
            [null, false, false, null],
            [null, true, false, self::CUSTOMER_EMAIL],
            [self::CUSTOMER_EMAIL, true, true, self::CUSTOMER_EMAIL]
        ];
    }
}
