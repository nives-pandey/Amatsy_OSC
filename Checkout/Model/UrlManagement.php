<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Magento\Backend\Model\Url;

/**
 * Class UrlManagement
 */
class UrlManagement extends Url
{
    /**
     * @inheritdoc
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $this->getRouteParamsResolver()->unsetData('route_params');

        return parent::getUrl($routePath, $routeParams);
    }
}
