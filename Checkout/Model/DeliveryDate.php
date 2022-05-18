<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

class DeliveryDate
{
    private const XPATH_FIRST_DAY = 'general/locale/firstday';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->timezone->getDateFormat(\IntlDateFormatter::SHORT);
    }

    /**
     * @return int
     */
    public function getFirstDay(): int
    {
        return (int)$this->scopeConfig->getValue(self::XPATH_FIRST_DAY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getDeliveryDays()
    {
        $days = $this->scopeConfig->getValue(
            'amasty_checkout/delivery_date/available_days',
            ScopeInterface::SCOPE_STORE
        );

        if (!$days) {
            return [];
        }

        $days = explode(',', $days);

        foreach ($days as &$day) {
            $day = (int)$day;
        }

        return $days;
    }

    /**
     * @return array
     */
    public function getDeliveryHours()
    {
        $hoursSetting = trim($this->scopeConfig->getValue(
            'amasty_checkout/delivery_date/available_hours',
            ScopeInterface::SCOPE_STORE
        ));

        $intervals = preg_split('#\s*,\s*#', $hoursSetting, -1, PREG_SPLIT_NO_EMPTY);

        $hours = $this->getHours($intervals);

        if (!$hours) {
            $hours = range(0, 23);
        } else {
            $hours = array_unique($hours);
            asort($hours);
        }

        $options = [[
                        'value' => '-1',
                        'label' => ' ',
                    ]];

        foreach ($hours as $hour) {
            $options [] = [
                'value' => $hour,
                'label' => $hour . ':00 - ' . (($hour) + 1) . ':00',
            ];
        }

        return $options;
    }

    /**
     * @param array $hours
     * @param array $range
     *
     * @return array
     */
    private function mergeHours($hours, $range)
    {
        return array_merge($hours, $range);
    }

    /**
     * @param array $intervals
     *
     * @return array
     */
    private function getHours($intervals)
    {
        $hours = [];

        foreach ($intervals as $interval) {
            if (preg_match('#(?P<lower>\d+)(\s*-\s*(?P<upper>\d+))?#', $interval, $matches)) {
                $lower = (int)$matches['lower'];
                if ($lower > 23) {
                    continue;
                }

                if (isset($matches['upper'])) {
                    $upper = (int)$matches['upper'];
                    if ($upper > 24) {
                        continue;
                    }

                    $upper--;

                    if ($lower > $upper) {
                        continue;
                    }
                } else {
                    $upper = $lower;
                }

                $range = range($lower, $upper);
                $hours = $this->mergeHours($hours, $range);
            }
        }

        return $hours;
    }
}
