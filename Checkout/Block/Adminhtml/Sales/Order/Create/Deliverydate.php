<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Adminhtml\Sales\Order\Create;

use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\Delivery as DeliveryModel;
use Amasty\Checkout\Model\DeliveryDate as DeliveryDateModel;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;

class Deliverydate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var DeliveryDateModel
     */
    private $deliveryDate;

    /**
     * @var DeliveryModel
     */
    private $delivery;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Quote
     */
    private $sessionQuote;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Context $context,
        DeliveryDateModel $deliveryDate,
        FormFactory $formFactory,
        DeliveryModel $delivery,
        Quote $sessionQuote,
        DateTime $date,
        Config $config,
        array $data = []
    ) {
        $this->deliveryDate = $deliveryDate;
        $this->sessionQuote = $sessionQuote;
        $this->delivery = $delivery;
        $this->formFactory = $formFactory;
        $this->date = $date;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('Amasty_Checkout::sales/order/delivery_create.phtml');
    }

    public function getFormElements()
    {
        $form = $this->formFactory->create();
        $form->setHtmlIdPrefix('amasty_checkout_deliverydate_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Delivery'),
                'class' => 'amasty-checkout-deliverydate-fieldset'
            ]
        );

        $fieldset->addField(
            'date',
            'date',
            [
                'label' => __('Delivery Date'),
                'name' => 'am_checkout_deliverydate[date]',
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'style' => 'width: 40%',
                'format' => 'y-MM-dd',
                'required' => $this->config->getDeliveryDateConfig('date_required'),
                'date_format' => 'y-MM-dd',
                'min_date' => $this->date->date('c'),
                'value' => null
            ]
        );

        $deliveryHours = $this->getDeliveryHours();
        $fieldset->addField(
            'time',
            'select',
            [
                'label' => __('Delivery Time Interval'),
                'name' => 'am_checkout_deliverydate[time]',
                'style' => 'width: 40%',
                'required' => false,
                'value' => null,
                'options' => $deliveryHours,
            ]
        );

        if ($this->config->getDeliveryDateConfig('delivery_comment_enable')) {
            $comment = (string)$this->config->getDeliveryDateConfig('delivery_comment_default');
            $fieldset->addField(
                'comment',
                'textarea',
                [
                    'label' => __('Delivery Comment'),
                    'title' => __('Delivery Comment'),
                    'name' => 'am_checkout_deliverydate[comment]',
                    'required' => false,
                    'style' => 'width: 40%',
                    'placeholder' => $comment
                ]
            );
        }

        $data = $this->getDeliveryInfo();

        if (!empty($data)) {
            if (isset($data['date']) && '0000-00-00' == $data['date']) {
                $data['date'] = '';
            }

            if (isset($data['time']) && !isset($deliveryHours[$data['time']])) {
                $data['time'] = -1;
            }

            $form->setValues($data);
        }

        return $form->getElements();
    }

    public function getDeliveryInfo()
    {
        $orderId = 0;

        if ($this->sessionQuote->getOrderId()) { // edit order
            $orderId = $this->sessionQuote->getOrderId();
        } elseif ($this->sessionQuote->getReordered()) { // reorder
            $orderId = $this->sessionQuote->getReordered();
        }

        $delivery = $this->delivery->findByOrderId($orderId);

        return $delivery->getData();
    }

    public function getDeliveryHours()
    {
        $options = $this->deliveryDate->getDeliveryHours();

        if (empty($options)) {
            return $options;
        }

        $deliveryHours = [];

        foreach ($options as $option) {
            $deliveryHours[$option['value']] = $option['label'];
        }

        return $deliveryHours;
    }
}
