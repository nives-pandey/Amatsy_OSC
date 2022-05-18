<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Model\Config;
use Amasty\Geoip\Model\Geolocation;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

/**
 * Class FieldsDefaultProvider
 */
class FieldsDefaultProvider
{
    protected $defaultData = null;

    /**
     * @var Config
     */
    private $checkoutConfig;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var CollectionFactory
     */
    private $regionCollectionFactory;

    public function __construct(
        Config $checkoutConfig,
        Geolocation $geolocation,
        RemoteAddress $remoteAddress,
        CollectionFactory $regionCollectionFactory
    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->geolocation = $geolocation;
        $this->remoteAddress = $remoteAddress;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * Return default address data
     *
     * @return array
     */
    public function getDefaultData()
    {
        if ($this->defaultData === null) {
            $this->defaultData = [];

            $defaultValues = $this->checkoutConfig->getDefaultValues();

            if (is_array($defaultValues)) {
                foreach ($defaultValues as $code => $value) {
                    if (preg_match('#^address_(?P<field>.+)$#', $code, $matches)) {
                        $this->defaultData[$matches['field']] = $value;
                    }
                }
            }
            $this->getAddressDataByGeolocation();

            if (!empty($this->defaultData['region_id'])) {
                /** @var \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection */
                $regionCollection = $this->regionCollectionFactory->create();
                $regionCollection->addCountryFilter($this->defaultData['country_id'])
                    ->addFieldToFilter(
                        ['main_table.region_id', 'main_table.default_name'],
                        [['eq' => $this->defaultData['region_id']], ['eq' => $this->defaultData['region_id']]]
                    )
                    ->setPageSize(1);

                /** @var \Magento\Directory\Model\Region $regionModel */
                $regionModel = $regionCollection->getFirstItem();
                if ($regionModel->getId()) {
                    $this->defaultData['region_id'] = $regionModel->getId();
                } else {
                    $this->defaultData['region'] = $this->defaultData['region_id'];
                    unset($this->defaultData['region_id']);
                }
            }
        }

        return $this->defaultData;
    }

    public function getAddressDataByGeolocation()
    {
        if ($this->checkoutConfig->isGeolocationEnabled()) {
            $ip = $this->remoteAddress->getRemoteAddress();
            $geolocationData = $this->geolocation->locate($ip);

            if ($geolocationData->getData('country')) {
                $this->defaultData['country_id'] = $geolocationData->getData('country');
            }
            if ($geolocationData->getData('region')) {
                $this->defaultData['region_id'] = $geolocationData->getData('region');
            } else {
                unset($this->defaultData['region_id']);
            }
            if ($geolocationData->getData('city')) {
                $this->defaultData['city'] = $geolocationData->getData('city');
            }
//            if ($geolocationData->getData('postal_code')) {
//                  /*database data can be extremely old, postal_code is excluded from results (locate function*/
//                $this->defaultData['postcode'] = $geolocationData->getData('postal_code');
//            }
        }
    }
}
