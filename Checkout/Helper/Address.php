<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Helper;

use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\Field;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Address for fill empty fields
 */
class Address extends AbstractHelper
{
    /**
     * @var Field
     */
    protected $fieldSingleton;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $directoryData;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Field $fieldSingleton,
        Data $directoryData,
        Config $configProvider
    ) {
        parent::__construct($context);
        $this->fieldSingleton = $fieldSingleton;
        $this->storeManager = $storeManager;
        $this->directoryData = $directoryData;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function fillEmpty(\Magento\Quote\Model\Quote\Address $address)
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $fieldConfig = $this->fieldSingleton->getConfig(
            $this->storeManager->getStore()->getId()
        );

        $requiredFields = [
            'firstname',
            'lastname',
            'street',
            'city',
            'telephone',
            'postcode',
            'country_id',
        ];

        foreach ($requiredFields as $code) {
            if (!isset($fieldConfig[$code])) {
                continue;
            }

            /** @var \Amasty\Checkout\Model\Field $field */
            $field = $fieldConfig[$code];

            if (((!$address->hasData($code) || $address->getData($code) == 0) && !$field->getData('enabled'))
                ||
                ($address->hasData($code) && !$address->getData($code) && !$field->getData('required'))
            ) {
                $defaultValue = '-';

                switch ($code) {
                    case 'country_id':
                        $defaultValue = $this->configProvider->getDefaultCountryId();
                        break;
                    case 'telephone':
                        $defaultValue = '';
                        break;
                    case 'region_id':
                        if ($this->directoryData->isRegionRequired($address->getCountryId())) {
                            $defaultValue = $this->configProvider->getDefaultRegionId($address);
                        } else {
                            continue 2;
                        }
                        break;
                }

                $address->setData($code, $defaultValue);
            }
        }
    }
}
