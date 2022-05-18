<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Amasty\Checkout\Model\ResourceModel\Fee\CollectionFactory as FeeCollectionFactory;

/**
 * Class Additional
 */
class Additional extends AbstractTotal
{
    /**
     * @var FeeCollectionFactory
     */
    protected $feeCollectionFactory;

    public function __construct(
        FeeCollectionFactory $feeCollectionFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->feeCollectionFactory = $feeCollectionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     *
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $feesQuoteCollection = $this->feeCollectionFactory->create()
            ->addFieldToFilter('quote_id', $order->getQuoteId());

        $feeAmount = 0;
        $baseFeeAmount = 0;

        /** @var \Amasty\Checkout\Model\Fee $fee */
        foreach ($feesQuoteCollection as $fee) {
            $feeAmount += $fee->getData('amount');
            $baseFeeAmount += $fee->getData('base_amount');
        }

        $invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmount);

        return $this;
    }
}
