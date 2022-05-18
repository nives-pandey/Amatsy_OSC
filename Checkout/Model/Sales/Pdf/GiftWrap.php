<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Sales\Pdf;

use Amasty\Checkout\Api\FeeRepositoryInterface;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

/**
 * Class GiftWrap
 */
class GiftWrap extends DefaultTotal
{
    /**
     * @var FeeRepositoryInterface
     */
    protected $feeRepository;

    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        FeeRepositoryInterface $feeRepository,
        array $data = []
    ) {

        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->feeRepository = $feeRepository;
    }

    public function getAmount()
    {
        $fee = $this->feeRepository->getByOrderId($this->getSource()->getOrderId());

        if (!$fee->getData()) {
            return null;
        }

        return $fee->getAmount();
    }

    /**
     * @inheritdoc
     */
    public function canDisplay()
    {
        $amount = $this->getAmount();

        return $this->getDisplayZero() === 'true' && $amount !== null;
    }
}
