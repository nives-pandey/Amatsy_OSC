<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Checkout\Block\Adminhtml\Reports\Filters;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\Date;
use Amasty\Checkout\Model\Field;
use Amasty\Checkout\Model\FieldFactory;
use Amasty\Checkout\Plugin\DefaultConfigProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Amasty\Checkout\Model\Quote\ResourceModel\CollectionFactory as QuotesFactory;
use Magento\Quote\Model\ResourceModel\Quote\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\Order\Address;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory as PaymentsFactory;
use Amasty\Checkout\Model\ResourceModel\Delivery\CollectionFactory as DeliveriesFactory;
use Amasty\Checkout\Model\ResourceModel\Delivery\Collection as DeliveryCollection;

/**
 * Class StatisticManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @codingStandardsIgnoreStart
 */
class StatisticManagement
{
    const ADDRESSES = [
        Address::TYPE_SHIPPING,
        Address::TYPE_BILLING
    ];

    const DELIVERY_INFO = [
        'date' => 'Delivery Date',
        'time' => 'Delivery Time',
        'comment' => 'Delivery Comment',
    ];

    const KLARNA_METHODS = [
        'klarna_pay_now',
        'klarna_pay_later',
        'klarna_pay_over_time',
        'klarna_direct_debit',
        'klarna_direct_bank_transfer'
    ];

    /**
     * @var array
     */
    private $quoteIds = [];

    /**
     * @var array
     */
    private $statisticData = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var QuoteCollection
     */
    private $quotesCollection;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var PaymentsFactory
     */
    private $paymentsFactory;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var DeliveriesFactory
     */
    private $deliveriesFactory;

    /**
     * @var \Amasty\Checkout\Model\Config
     */
    private $config;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Config\CheckoutBlocksProvider
     */
    private $checkoutBlocksProvider;

    public function __construct(
        FieldFactory $fieldFactory,
        QuotesFactory $quotesFactory,
        CollectionFactory $addressCollectionFactory,
        PaymentsFactory $paymentsFactory,
        DeliveriesFactory $deliveriesFactory,
        RequestInterface $request,
        Date $date,
        ResourceConnection $resourceConnection,
        Data $data,
        Config $config,
        Serializer $serializer,
        ManagerInterface $messageManager,
        Config\CheckoutBlocksProvider $checkoutBlocksProvider
    ) {
        $this->quotesCollection = $quotesFactory->create();
        $this->fieldFactory = $fieldFactory;
        $this->request = $request;
        $this->date = $date;
        $this->resourceConnection = $resourceConnection;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->paymentsFactory = $paymentsFactory;
        $this->data = $data;
        $this->deliveriesFactory = $deliveriesFactory;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
        $this->checkoutBlocksProvider = $checkoutBlocksProvider;
    }

    /**
     * @return array
     */
    public function calculateStatistic()
    {
        if (empty($this->statisticData)) {
            try {
                $this->setParams();
                $this->collectQuotesSize();
                $this->collectCustomerData();
                $this->collectAddressData();
                $this->collectShippingData();
                $this->collectPaymentData();
                $this->collectDeliveryData();
                $this->getGraphicInfo();
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage($exception);
            }
        }

        return $this->statisticData;
    }

    /**
     * add graphic info
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getGraphicInfo()
    {
        $store = isset($this->params['store']) && $this->params['store'] !== Filters::ALL
            ? $this->params['store']
            : null;

        $checkoutBlocksConfig = $this->config->getCheckoutBlocksConfig($store);
        foreach ($this->checkoutBlocksProvider->getDefaultBlockTitles() as $blockCode => $blockLabel) {
            if ($blockCode === 'summary') {
                continue;
            }

            $blockCustomLabel = $this->getBlockCustomLabel($blockCode, $checkoutBlocksConfig);
            $this->statisticData['data']['graphic_info'][] = [
                'label' => $blockCustomLabel ?: (string)__($blockLabel),
                'size' => isset($this->statisticData['data'][$blockCode . '_total_count'])
                    ? $this->getRate($this->statisticData['data'][$blockCode . '_total_count'], 0) : 0
            ];
        }
    }

    /**
     * @param string $blockCode
     * @param array $checkoutBlocksConfig
     * @return string
     */
    private function getBlockCustomLabel($blockCode, $checkoutBlocksConfig)
    {
        $blockCode = str_replace('block_', '', $blockCode);

        foreach ($checkoutBlocksConfig as $column) {
            foreach ($column as $block) {
                if ($block['name'] === $blockCode) {
                    return $block['title'];
                }
            }
        }

        return '';
    }

    /**
     * add delivery info to statistic
     */
    private function collectDeliveryData()
    {
        $conditions = [];
        $isDeliveryRequired = $this->config->getDeliveryDateConfig('date_required');
        /** @var DeliveryCollection $delivery */
        $delivery = $this->deliveriesFactory->create();
        $delivery->addFieldToFilter('quote_id', ['in' => $this->quoteIds]);
        $delivery->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $delivery->getSelect()->columns(['size' => new \Zend_Db_Expr('COUNT(*)')]);
        $totalCount = clone $delivery;

        foreach (self::DELIVERY_INFO as $code => $label) {
            $clone = clone $delivery;
            $clone->addFieldToFilter($code, ['notnull' => true]);
            $size = $clone->fetchItem()->getSize();
            $this->statisticData['data']['delivery'][] = [
                'size' => $size,
                'label' => __($label)->getText(),
                'rate' => $this->getRate($size)
            ];

            if (!$isDeliveryRequired) {
                $conditions[] = ['notnull' => true];
            }
        }

        if ($isDeliveryRequired) {
            $totalCount->addFieldToFilter('date', ['notnull' => true]);
        } else {
            $totalCount->addFieldToFilter(array_keys(self::DELIVERY_INFO), $conditions);
        }

        $this->statisticData['data']['delivery_total_count'] = $totalCount->getSize();
    }

    /**
     * add payment method to statistic
     */
    private function collectPaymentData()
    {
        $totalCount = 0;
        $preparedMethods = [];

        /** @var PaymentCollection $payments */
        $payments = $this->paymentsFactory->create();
        $payments->addFieldToFilter('quote_id', ['in' => $this->quoteIds])
        ->addFieldToFilter('method', ['notnull' => true])
        ->addFieldToFilter('method', ['neq' => '-']);
        $payments->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $payments->getSelect()->columns(['size' => new \Zend_Db_Expr('COUNT(*)')]);
        $payments->getSelect()->columns(['payment_code' => 'method']);
        $payments->getSelect()->group('method');
        $methods = $payments->getData();
        $allPayments = $this->data->getPaymentMethodList();

        foreach ($methods as $method) {
            if (in_array($method['payment_code'], self::KLARNA_METHODS)) {
                $method['payment_code'] = 'klarna_kp';
            }

            if (isset($allPayments[$method['payment_code']])) {
                $preparedMethods[] = [
                    'size' => $method['size'],
                    'payment_code' => $method['payment_code'],
                    'label' => $allPayments[$method['payment_code']],
                    'rate' => $this->getRate($method['size'])
                ];
                $totalCount += $method['size'];
            }
        }

        $this->statisticData['data']['payment_method'] = $preparedMethods;
        $this->statisticData['data']['payment_method_total_count'] = $totalCount;
    }

    /**
     * add shipping method to statistic
     */
    private function collectShippingData()
    {
        $totalCount = 0;

        /** @var QuoteCollection $addresses */
        $addresses = $this->getAddressCollection();
        $addresses->getSelect()->columns(['label' => 'shipping_description']);
        $addresses->getSelect()->columns(['size' => new \Zend_Db_Expr('COUNT(*)')]);
        $addresses->addFieldToFilter('shipping_method', ['notnull' => true]);
        $addresses->addFieldToFilter('shipping_method', ['neq' => '-']);
        $addresses->getSelect()->group('shipping_method');
        $methods = $addresses->getData();

        foreach ($methods as &$method) {
            $method['rate'] = $this->getRate($method['size']);
            $totalCount += $method['size'];
        }

        $this->statisticData['data']['shipping_method'] = $methods;
        $this->statisticData['data']['shipping_method_total_count'] = $totalCount;
    }

    /**
     * calculate size of quotes collection
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function collectQuotesSize()
    {
        if (isset($this->params['store']) && $this->params['store'] !== Filters::ALL) {
            $this->quotesCollection->addFieldToFilter('main_table.store_id', $this->params['store']);
        }

        if (isset($this->params['customer_group']) && $this->params['customer_group'] !== Filters::ALL) {
            $this->quotesCollection->addFieldToFilter('main_table.customer_group_id', $this->params['customer_group']);
        }

        if (isset($this->params['date_range']) && $this->params['date_range'] == Filters::CUSTOM) {
            $this->quotesCollection->addFieldToFilter(
                'main_table.updated_at',
                ['lt' => $this->date->date(null, $this->params['date_to'])]
            )->addFieldToFilter(
                'main_table.updated_at',
                ['gt' => $this->date->date(null, $this->params['date_from'])]
            );
        }

        if (isset($this->params['date_range'])
            && $this->params['date_range'] != Filters::CUSTOM
            && $this->params['date_range'] != Filters::OVERALL
        ) {
            $this->quotesCollection->addFieldToFilter(
                'main_table.updated_at',
                ['lt' => $this->date->getDateWithOffsetByDays(1)]
            )->addFieldToFilter(
                'main_table.updated_at',
                ['gt' => $this->date->getDateWithOffsetByDays((-1) * ($this->params['date_range'] - 1))]
            );
        }

        $this->statisticData['data']['quote_total_count'] = $this->quotesCollection->getSize();
    }

    /**
     * add customer email to statistic
     */
    private function collectCustomerData()
    {
        /** @var QuoteCollection $addresses */
        $addresses = $this->getAddressCollection();
        $addresses->addFieldToFilter('email', ['notnull' => true]);
        $addresses->addFieldToFilter('email', ['neq' => '-']);
        $addresses->addFieldToFilter('address_type', ['eq' => Address::TYPE_SHIPPING]);
        $size = $addresses->getSize();
        $this->statisticData['data'][Address::TYPE_SHIPPING . '_address'][] = [
            'label' => __('Customer Email'),
            'size' => $size,
            'rate' => $this->getRate($size)
        ];
        $this->statisticData['email_count'] = $size;
    }

    /**
     * collect address data
     */
    private function collectAddressData()
    {
        $fields = $this->fieldFactory->create()->getAttributeCollectionByStoreId()->getItems();
        /** @var QuoteCollection $addresses */
        $addresses = $this->getAddressCollection();
        $filledQuote = clone $this->getAddressCollection();
        $filledQuote->addFieldToFilter('address_type', Address::TYPE_SHIPPING);

        foreach (self::ADDRESSES as $addressType) {
            /** @var Field $field */
            foreach ($fields as $field) {
                $address = clone $addresses;
                try {
                    $address->addFieldToFilter('address_type', $addressType);
                    $address->addFieldToFilter($field->getAttributeCode(), ['notnull' => true]);
                    $address->addFieldToFilter($field->getAttributeCode(), ['neq' => '-']);
                    $size = $address->getSize();
                    $this->statisticData['data'][$addressType . '_address'][] = [
                        'label' => $field['label'],
                        'size' => $size,
                        'rate' => $this->getRate($size)
                    ];

                    if ($field->isRequired() && $field->getEnabled() && $addressType === Address::TYPE_SHIPPING) {
                        $filledQuote->addFieldToFilter($field['attribute_code'], ['notnull' => true]);
                        $filledQuote->addFieldToFilter($field['attribute_code'], ['neq' => '-']);
                    }
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }

        $this->statisticData['data']['shipping_address_total_count'] =
            $filledQuote->getSize() < $this->statisticData['email_count']
                ? $filledQuote->getSize()
                : $this->statisticData['email_count'];
    }

    /**
     * @return QuoteCollection
     */
    private function getAddressCollection()
    {
        /** @var QuoteCollection $address */
        $address = $this->addressCollectionFactory->create();
        $quotesCollection = clone $this->quotesCollection;
        $quotesCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('entity_id');
        $this->quoteIds = $quotesCollection->getConnection()->fetchCol($quotesCollection->getSelect());

        $address->addFieldToFilter(
            'main_table.quote_id',
            [
                'in' => $this->quoteIds
            ]
        );
        $address->getSelect()->reset(\Zend_Db_Select::COLUMNS);

        return $address;
    }

    /**
     * @param int $size
     *
     * @param int $precision
     *
     * @return float
     */
    private function getRate($size, $precision = 2)
    {
        if (!$size) {
            return 0;
        }

        return round($size / $this->statisticData['data']['quote_total_count'] * 100, $precision);
    }

    /**
     * parse GET params (filters)
     */
    private function setParams()
    {
        parse_str($this->request->getParam('filters'), $this->params);
    }
}
