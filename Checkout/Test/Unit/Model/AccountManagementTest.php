<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\AccountManagement;
use Amasty\Checkout\Test\Unit\Traits;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AccountManagementTest
 *
 * @see AccountManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AccountManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ReflectionTrait;
    use Traits\ObjectManagerTrait;

    const CAN_CREATE = '2';
    const CANNOT_CREATE = 2;

    const ORDER_ID = 10;

    const STORE_ID = 1;
    const WEBSITE_ID = 2;
    const TAXVAT = 'vat';

    const PASSWORD_HASH = 'pAssWoRdHaSh';

    /**
     * @covers AccountManagement::createAccount
     */
    public function testCreateAccountConfig()
    {
        $this->assertFalse(
            $this->getObjectManager()->getObject(
                AccountManagement::class,
                ['config' => $this->getConfig(static::CANNOT_CREATE)]
            )->createAccount($this->getOrder())
        );
    }

    /**
     * @covers AccountManagement::createAccount
     * @throws \Exception
     */
    public function testCreateAccount()
    {
        $customerValidator = $this->createPartialMock(\Amasty\Checkout\Model\CustomerValidator::class, ['validateByDataObject']);
        $customerValidator->expects($this->once())->method('validateByDataObject')->willReturn(true);

        $orderRepository = $this->createPartialMock(\Magento\Sales\Model\OrderRepository::class, ['save']);
        $orderRepository->expects($this->once())->method('save');

        $eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);
        $eventManager->expects($this->once())->method('dispatch');

        $customerGroupId = 1;
        $defaultGroupMock = $this->createMock(\Magento\Customer\Api\Data\GroupInterface::class);
        $defaultGroupMock->method('getId')->willReturn($customerGroupId);

        $groupManagementMock = $this->createMock(GroupManagementInterface::class);
        $groupManagementMock->method('getDefaultGroup')->willReturn($defaultGroupMock);

        /** @var AccountManagement $model */
        $model = $this->getObjectManager()->getObject(
            AccountManagement::class,
            [
                'orderRepository' => $orderRepository,
                'accountManagement' => $this->getAccountManagement(),
                'customerExtractor' => $this->getCustomerExtractor(),
                'config' => $this->getConfig(static::CAN_CREATE),
                'fieldsManagement' => $this->getFieldsManagement(),
                'timezone' => $this->getTimezone(),
                'customerValidator' => $customerValidator,
                'storeManager' => $this->getStoreManager(),
                'quotePasswordsRepository' => $this->getQuotePasswordsRepository(),
                'eventManager' => $eventManager,
                'groupManagement' => $groupManagementMock
            ]
        );

        /** @var \Magento\Customer\Model\Data\Customer $result */
        $result = $model->createAccount($this->getOrder());
        $resultData = $this->getProperty($result, '_data', \Magento\Customer\Model\Data\Customer::class);

        $this->assertInstanceOf(\Magento\Customer\Model\Data\Customer::class, $result);
        $this->assertArrayHasKey(CustomerInterface::STORE_ID, $resultData);
        $this->assertArrayHasKey(CustomerInterface::WEBSITE_ID, $resultData);
        $this->assertArrayHasKey(CustomerInterface::TAXVAT, $resultData);
        $this->assertArrayHasKey(CustomerInterface::GROUP_ID, $resultData);

        $this->assertEquals(static::STORE_ID, $resultData[CustomerInterface::STORE_ID]);
        $this->assertEquals(static::WEBSITE_ID, $resultData[CustomerInterface::WEBSITE_ID]);
        $this->assertEquals(static::TAXVAT, $resultData[CustomerInterface::TAXVAT]);
        $this->assertEquals($customerGroupId, $resultData[CustomerInterface::GROUP_ID]);
    }

    public function testSavePassword()
    {
        /** @var \Magento\Quote\Model\QuoteIdMask|MockObject $quoteIdMask */
        $quoteIdMask = $this->createPartialMock(\Magento\Quote\Model\QuoteIdMask::class, ['load']);
        $quoteIdMask->expects($this->once())->method('load')->willReturnSelf();
        $quoteIdMask->setQuoteId(1);

        $maskQuoteFactory = $this->createMock(\Magento\Quote\Model\QuoteIdMaskFactory::class);
        $maskQuoteFactory->expects($this->once())->method('create')->willReturn($quoteIdMask);

        /** @var \Amasty\Checkout\Model\QuotePasswords|MockObject $modelQuotePasswords */
        $modelQuotePasswords = $this->createPartialMock(\Amasty\Checkout\Model\QuotePasswords::class, []);

        /** @var \Amasty\Checkout\Model\QuotePasswordsRepository|MockObject $repositoryQuotePasswords */
        $repositoryQuotePasswords = $this->createPartialMock(
            \Amasty\Checkout\Model\QuotePasswordsRepository::class,
            ['getByQuoteId', 'save']
        );
        $repositoryQuotePasswords->expects($this->once())->method('getByQuoteId')->willReturnReference($modelQuotePasswords);
        $repositoryQuotePasswords->expects($this->once())->method('save');

        $encryptor = $this->createMock(\Magento\Framework\Encryption\Encryptor::class);
        $encryptor->expects($this->once())->method('getHash')->willReturn(static::PASSWORD_HASH);

        /** @var AccountManagement $model */
        $model = $this->getObjectManager()->getObject(
            AccountManagement::class,
            [
                'config' => $this->getConfig(static::CAN_CREATE),
                'quotePasswordsRepository' => $repositoryQuotePasswords,
                'quoteIdMaskFactory' => $maskQuoteFactory,
                'encryptor' => $encryptor,
            ]
        );

        $model->savePassword(1, 111);
        $this->assertEquals(static::PASSWORD_HASH, $modelQuotePasswords->getPasswordHash());
        $this->assertEquals(1, $modelQuotePasswords->getQuoteId());
    }

    /**
     * @param $returnValue
     *
     * @return MockObject
     */
    private function getConfig($returnValue)
    {
        $config = $this->createMock(\Amasty\Checkout\Model\Config::class);
        $config->expects($this->once())->method('getAdditionalOptions')->willReturn($returnValue);

        return $config;
    }

    private function getCustomerExtractor()
    {
        /** @var \Magento\Customer\Model\Data\Customer|MockObject $customer */
        $customer = $this->createPartialMock(\Magento\Customer\Model\Data\Customer::class, []);

        /** @var \Amasty\Checkout\Model\AdditionalFieldsManagement|MockObject $fieldsManagement */
        $customerExtractor = $this->createPartialMock(\Amasty\Checkout\Model\Sales\OrderCustomerExtractor::class, ['extract']);
        $customerExtractor->expects($this->once())->method('extract')->willReturn($customer);

        return $customerExtractor;
    }

    /**
     * @return \Amasty\Checkout\Model\AdditionalFieldsManagement|MockObject
     * @throws \Exception
     */
    private function getFieldsManagement()
    {
        /** @var \Amasty\Checkout\Model\AdditionalFields|MockObject $field */
        $field = $this->createPartialMock(\Amasty\Checkout\Model\AdditionalFields::class, []);
        $field->setDateOfBirth(new \DateTime());

        /** @var \Amasty\Checkout\Model\AdditionalFieldsManagement|MockObject $fieldsManagement */
        $fieldsManagement = $this->createPartialMock(\Amasty\Checkout\Model\AdditionalFieldsManagement::class, ['getByQuoteId']);
        $fieldsManagement->expects($this->once())->method('getByQuoteId')->willReturn($field);

        return $fieldsManagement;
    }

    /**
     * @return MockObject
     */
    private function getTimezone()
    {
        $timezone = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $timezone->expects($this->once())->method('date')->willReturnArgument(0);

        return $timezone;
    }

    /**
     * @return \Magento\Sales\Model\Order|MockObject
     * @TODO: rewrite test
     */
    private function getOrder()
    {
        /** @var \Magento\Sales\Model\Order\Address|MockObject $address */
        $address = $this->createPartialMock(\Magento\Sales\Model\Order\Address::class, ['getVatId']);
        $address->expects($this->any())->method('getVatId')->willReturn(static::TAXVAT);

        /** @var \Magento\Sales\Model\Order|MockObject $order */
        $order = $this->createPartialMock(\Magento\Sales\Model\Order::class, ['getBillingAddress', 'getShippingAddress']);
        $order->expects($this->any())->method('getBillingAddress')->willReturnReference($address);
        $order->expects($this->any())->method('getShippingAddress')->willReturnReference($address);
        $order->setId(static::ORDER_ID);
        $order->setShippingMethod('shipping_method');

        return $order;
    }

    /**
     * @return \Magento\Store\Model\StoreManager|MockObject
     */
    private function getStoreManager()
    {
        /** @var \Magento\Store\Model\Store|MockObject $store */
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getCode', 'getId', 'getWebsiteId']);
        $store->expects($this->any())->method('getId')->willReturn(self::STORE_ID);
        $store->expects($this->any())->method('getCode')->willReturn('default');
        $store->expects($this->any())->method('getWebsiteId')->willReturn(self::WEBSITE_ID);

        /** @var \Magento\Store\Model\Website|MockObject $website */
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getDefaultStore']);
        $website->expects($this->any())->method('getDefaultStore')->willReturn($store);

        /** @var \Magento\Store\Model\StoreManager|MockObject $storeManager */
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $storeManager->expects($this->any())->method('getWebsite')->willReturn($website);
        $storeManager->expects($this->any())->method('getStores')->willReturn([ 'default' => $store ]);

        return $storeManager;
    }

    /**
     * @return \Amasty\Checkout\Model\QuotePasswordsRepository|MockObject
     */
    private function getQuotePasswordsRepository()
    {
        $model = $this->createMock(\Amasty\Checkout\Model\QuotePasswords::class);
        $model->expects($this->once())->method('getPasswordHash')->willReturn(self::PASSWORD_HASH);
        $model->expects($this->once())->method('hasData')->willReturn(true);

        /** @var \Amasty\Checkout\Model\QuotePasswordsRepository|MockObject $repository */
        $repository = $this->createPartialMock(\Amasty\Checkout\Model\QuotePasswordsRepository::class, ['getByQuoteId', 'delete']);
        $repository->expects($this->exactly(2))->method('getByQuoteId')->willReturn($model);
        $repository->expects($this->once())->method('delete');

        return $repository;
    }

    /**
     * @return \Magento\Customer\Model\AccountManagement|MockObject
     */
    private function getAccountManagement()
    {
        /** @var \Magento\Customer\Model\AccountManagement|MockObject $accountManagement */
        $accountManagement = $this->createPartialMock(
            \Magento\Customer\Model\AccountManagement::class,
            ['isEmailAvailable', 'createAccountWithPasswordHash']
        );

        $accountManagement->expects($this->once())->method('isEmailAvailable')->willReturn(true);
        $accountManagement->expects($this->once())->method('createAccountWithPasswordHash')->willReturnArgument(0);

        return $accountManagement;
    }
}
