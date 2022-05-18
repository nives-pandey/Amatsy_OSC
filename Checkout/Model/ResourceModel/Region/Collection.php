<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\ResourceModel\Region;

/**
 * Class Collection
 */
class Collection extends \Magento\Directory\Model\ResourceModel\Region\Collection
{
    protected function _construct()
    {
        $this->_init(\Magento\Directory\Model\Region::class, \Magento\Directory\Model\ResourceModel\Region::class);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()], ['region_id', 'code', 'country_id']);
        
        return $this;
    }

    /**
     * @return array
     */
    public function fetchRegions()
    {
        $data = $this->getResource()->getConnection()->fetchAssoc($this->getSelect());

        $result = [];

        foreach ($data as $row) {
            $result[$row['country_id']][$row['code']] = $row['region_id'];
        }

        return $result;
    }
}
