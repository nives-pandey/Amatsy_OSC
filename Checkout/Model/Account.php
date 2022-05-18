<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Model\Order;

/**
 * Class Account
 */
class Account
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    public function __construct(
        ManagerInterface $messageManager,
        OrderCustomerManagementInterface $orderCustomerService,
        CustomerSession $customerSession,
        TimezoneInterface $timezone,
        OrderRepositoryInterface $orderRepository,
        EventManagerInterface $eventManager
    ) {
        $this->messageManager = $messageManager;
        $this->orderCustomerService = $orderCustomerService;
        $this->customerSession = $customerSession;
        $this->timezone = $timezone;
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
    }

    /**
     * @param int $orderId
     * @param AdditionalFields $fields
     */
    public function create($orderId, $fields)
    {
        if ($this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Customer is already registered'));

            return;
        }

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $orderId = $order->getId();

        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Your session has expired'));

            return;
        }

        try {
            $this->customerDobProcess($fields, $order);

            $account = $this->orderCustomerService->create($orderId);
            $this->eventManager->dispatch(
                'customer_register_success',
                [
                    'customer' => $account,
                    'amasty_checkout_register' => true
                ]
            );

            $this->messageManager->addSuccessMessage(
                __('Registration: A letter with further instructions will be sent to your email.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong with the registration.'));
        }
    }

    /**
     * @param AdditionalFields $fields
     * @param Order $order
     */
    private function customerDobProcess($fields, $order)
    {
        if ($fields->getDateOfBirth()) {
            $customerDob = $this->timezone->date($fields->getDateOfBirth())
                ->format(DateTime::DATETIME_PHP_FORMAT);
            $billingAddress = $order->getBillingAddress();
            $billingAddress->setCustomerDob($customerDob);
        }
    }
}
