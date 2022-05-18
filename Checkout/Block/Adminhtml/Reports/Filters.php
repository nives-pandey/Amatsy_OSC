<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Reports;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Amasty\Checkout\Model\Date;
use Amasty\Checkout\Model\ItemManagement;

/**
 * Class Filters
 */
class Filters extends Generic
{
    /**#@+
     * Data range
     */
    const LAST_DAY = 1;

    const LAST_WEEK = 7;

    const LAST_MONTH = 30;

    const OVERALL = 1000;

    const CUSTOM = 0;

    const ALL = 'all';
    /**#@-*/

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataObject
     */
    private $objectConverter;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var ItemManagement
     */
    private $itemManagement;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $objectConverter,
        Date $date,
        ItemManagement $itemManagement,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->date = $date;
        $this->itemManagement = $itemManagement;
    }

    /**
     * @return Generic
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('checkout_reports_');
        $params = $this->itemManagement->parseStr($this->_request->getParam('filters'));

        $form->addField(
            'website',
            'select',
            [
                'label' => __('Store View:'),
                'title' => __('Store View:'),
                'name' => 'store',
                'class' => 'checkout-reports-field',
                'values' => $this->getStoresArray(),
                'value' => isset($params['store']) ? $params['store'] : 0
            ]
        );

        $form->addField(
            'customer_group',
            'select',
            [
                'label' => __('Customer Group:'),
                'title' => __('Customer Group:'),
                'name' => 'customer_group',
                'class' => 'checkout-reports-field',
                'values' => $this->getCustomerGroupsArray(),
                'value' => isset($params['customer_group']) ? $params['customer_group'] : self::ALL
            ]
        );

        $form->addField(
            'date_range',
            'select',
            [
                'label' => __('Period:'),
                'title' => __('Period:'),
                'name' => 'date_range',
                'class' => 'checkout-reports-field',
                'values' => $this->getDateRangeArray(),
                'value' => isset($params['date_range']) ? $params['date_range'] : self::OVERALL
            ]
        );

        $form->addField(
            'date_from',
            'date',
            [
                'label' => __('From:'),
                'title' => __('From:'),
                'name' => 'date_from',
                'required' => true,
                'readonly' => true,
                'style' => 'display:none;',
                'class' => 'checkout-reports-field-date',
                'date_format' => 'M/d/Y',
                'value' => isset($params['date_from'])
                    ? $params['date_from']
                    : $this->date->getDateWithOffsetByDays(-5),
                'max_date' => $this->date->convertDate($this->date->getDateWithOffsetByDays(1)),
            ]
        );

        $form->addField(
            'date_to',
            'date',
            [
                'label' => __('To:'),
                'title' => __('To:'),
                'name' => 'date_to',
                'required' => true,
                'readonly' => true,
                'style' => 'display:none;',
                'class' => 'checkout-reports-field-date',
                'date_format' => 'M/d/Y',
                'value' => isset($params['date_to'])
                    ? $params['date_to']
                    : $this->date->getDateWithOffsetByDays(-5),
                'max_date' => $this->date->convertDate($this->date->getDateWithOffsetByDays(1))
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    private function getDateRangeArray()
    {
        return [
            [
                'value' => self::LAST_DAY,
                'label' => __('Today')
            ],
            [
                'value' => self::LAST_WEEK,
                'label' => __('Last 7 days')
            ],
            [
                'value' => self::LAST_MONTH,
                'label' => __('Last 30 days')
            ],
            [
                'value' => self::OVERALL,
                'label' => __('Overall')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom')
            ],
        ];
    }

    /**
     * @return array
     */
    private function getCustomerGroupsArray()
    {
        $customerGroups = $this->objectConverter->toOptionArray(
            $this->groupRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems(),
            'id',
            'code'
        );

        array_unshift($customerGroups, ['value' => self::ALL, 'label' => __('All Customer Groups')]);

        return $customerGroups;
    }

    /**
     * @return array
     */
    private function getStoresArray()
    {
        $stores = $this->objectConverter->toOptionArray(
            $this->_storeManager->getStores(),
            'store_id',
            'name'
        );

        array_unshift($stores, ['value' => self::ALL, 'label' => __('All Stores')]);

        return $stores;
    }
}
