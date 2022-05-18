<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Sales;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DataObject\Copy as CopyService;
use Magento\Customer\Model\Data\AddressFactory as AddressFactory;
use Magento\Customer\Model\Data\RegionFactory as RegionFactory;
use Magento\Customer\Model\Data\CustomerFactory as CustomerFactory;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Sales\Model\Order\Address as OrderAddress;

/**
 * Class OrderCustomerExtractor
 *
 * Extract customer data from an order.
 */
class OrderCustomerExtractor
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CopyService
     */
    private $objectCopyService;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        CopyService $objectCopyService,
        AddressFactory $addressFactory,
        RegionFactory $regionFactory,
        CustomerFactory $customerFactory,
        QuoteAddressFactory $quoteAddressFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->objectCopyService = $objectCopyService;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->customerFactory = $customerFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * Extract customer data from order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return CustomerInterface
     */
    public function extract(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        //Simply return customer from DB.
        if ($order->getCustomerId()) {
            return $this->customerRepository->getById($order->getCustomerId());
        }

        //Prepare customer data from order data if customer doesn't exist yet.
        $customerData = $this->objectCopyService->copyFieldsetToTarget(
            'order_address',
            'to_customer',
            $order->getBillingAddress(),
            []
        );

        $processedAddressData = [];
        $customerAddresses = [];

        /** @var \Magento\Sales\Model\Order\Address $orderAddress */
        foreach ($order->getAddresses() as $orderAddress) {
            $addressData = $this->objectCopyService
                ->copyFieldsetToTarget('order_address', 'to_customer_address', $orderAddress, []);

            $index = array_search($addressData, $processedAddressData);

            if ($index === false) {
                // create new customer address only if it is unique
                $customerAddress = $this->getCustomerAddress($addressData, $orderAddress);

                $processedAddressData[] = $addressData;
                $customerAddresses[] = $customerAddress;
                $index = count($processedAddressData) - 1;
            }

            $customerAddress = $customerAddresses[$index];
            // make sure that address type flags from equal addresses are stored in one resulted address
            if ($orderAddress->getAddressType() == OrderAddress::TYPE_BILLING) {
                $customerAddress->setIsDefaultBilling(true);
            }
            if ($orderAddress->getAddressType() == OrderAddress::TYPE_SHIPPING) {
                $customerAddress->setIsDefaultShipping(true);
            }
        }

        $customerData['addresses'] = $customerAddresses;

        return $this->customerFactory->create(['data' => $customerData]);
    }

    /**
     * @param array $addressData
     * @param \Magento\Sales\Model\Order\Address $orderAddress
     *
     * @return \Magento\Customer\Model\Data\Address
     */
    private function getCustomerAddress($addressData, $orderAddress)
    {
        /** @var \Magento\Customer\Model\Data\Address $customerAddress */
        $customerAddress = $this->addressFactory->create(['data' => $addressData]);
        $customerAddress->setIsDefaultBilling(false);
        $customerAddress->setIsDefaultBilling(false);

        if (is_string($orderAddress->getRegion())) {
            /** @var \Magento\Customer\Model\Data\Region $region */
            $region = $this->regionFactory->create();
            $region->setRegion($orderAddress->getRegion());
            $region->setRegionCode($orderAddress->getRegionCode());
            $region->setRegionId($orderAddress->getRegionId());
            $customerAddress->setRegion($region);
        }

        return $customerAddress;
    }
}
