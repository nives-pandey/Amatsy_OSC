<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Reports;

use Amasty\Checkout\Model\StatisticManagement;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData = [];

    /**
     * @var StatisticManagement
     */
    private $statisticManagement;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        StatisticManagement $statisticManagement,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->statisticManagement = $statisticManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->statisticManagement->calculateStatistic();
    }
}
