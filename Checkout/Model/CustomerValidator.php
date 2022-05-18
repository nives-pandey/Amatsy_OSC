<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Magento\Customer\Model\CustomerFactory;
use Magento\Eav\Model\Validator\Attribute\Data;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class CustomerValidator
 */
class CustomerValidator
{
    const ERROR_SESSION_INDEX = 'amasty_checkout_account_create_error';

    /**
     * @var array
     */
    private $arrayErrors = [];

    /**
     * @var Data
     */
    private $eavData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        Data $eavData,
        DataPersistorInterface $dataPersistor,
        CustomerFactory $customerFactory
    ) {
        $this->eavData = $eavData;
        $this->dataPersistor = $dataPersistor;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customerModel
     *
     * @return bool
     */
    public function validate(\Magento\Customer\Model\Customer $customerModel)
    {
        if (!$this->eavData->isValid($customerModel)) {
            $this->setErrorsMessage();

            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Customer\Model\Data\Customer $customer
     *
     * @return bool
     */
    public function validateByDataObject(\Magento\Customer\Model\Data\Customer $customer)
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->customerFactory->create();
        $customerModel->setData($customer->__toArray());
        $customerModel->getGroupId();

        return $this->validate($customerModel);
    }

    public function setErrorsMessage()
    {
        $errors = $this->eavData->getMessages();

        array_map([$this, 'convertErrorsToString'], $errors);

        if ($this->arrayErrors) {
            $this->dataPersistor->set(self::ERROR_SESSION_INDEX, implode(', ', $this->arrayErrors));
        }
    }

    /**
     * @param array $error
     */
    public function convertErrorsToString($error)
    {
        if (isset($error[0]) && $error[0] instanceof \Magento\Framework\Phrase) {
            $this->arrayErrors[] = $error[0]->__toString();
        }
    }
}
