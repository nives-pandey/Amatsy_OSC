<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Controller\Onepage;

use Amasty\Checkout\Model\CustomerValidator;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Checkout\Model\Account;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Api\AdditionalFieldsManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Message\ManagerInterface;
use Amasty\Checkout\Model\AdditionalFields;
use Magento\Checkout\Controller\Onepage\Success;

/**
 * Class SuccessPlugin
 */
class SuccessPlugin
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AdditionalFieldsManagementInterface
     */
    private $fieldsManagement;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var AdditionalFields
     */
    private $fields;

    public function __construct(
        Account $account,
        Config $config,
        AdditionalFieldsManagementInterface $fieldsManagement,
        Session $session,
        DataPersistorInterface $dataPersistor,
        ManagerInterface $messageManager
    ) {
        $this->account = $account;
        $this->config = $config;
        $this->fieldsManagement = $fieldsManagement;
        $this->session = $session;
        $this->dataPersistor = $dataPersistor;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Success $subject
     * @return null
     */
    public function beforeExecute(Success $subject)
    {
        if ($errors = $this->dataPersistor->get(CustomerValidator::ERROR_SESSION_INDEX)) {
            $this->messageManager->addExceptionMessage(
                new LocalizedException(__($errors)),
                __('Something went wrong while creating an account. Please contact us so we can assist you.')
            );

            $this->dataPersistor->clear(CustomerValidator::ERROR_SESSION_INDEX);
        }

        if (!$this->config->isEnabled()) {
            return null;
        }

        $order = $this->session->getLastRealOrder();

        if (!$order || $order->getCustomerId()) {
            return null;
        }

        $fields = $this->fieldsManagement->getByQuoteId($order->getQuoteId());

        $this->orderId = $order->getId();
        $this->fields = $fields;

        return null;
    }

    /**
     * @param Success $subject
     * @param \Magento\Framework\View\Result\Page $result
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function afterExecute(Success $subject, $result)
    {
        $fields = $this->fields;
        if ($fields && $fields->getRegister()) {
            $this->account->create($this->orderId, $fields);
        }

        return $result;
    }
}
