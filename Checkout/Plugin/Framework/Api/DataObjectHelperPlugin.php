<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Framework\Api;

use Amasty\Checkout\Plugin\Quote\Model\Cart\CartTotalRepository as CartTotalRepositoryPlugin;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\Data\TotalsInterface;

/**
 * Class DataObjectHelperPlugin
 */
class DataObjectHelperPlugin
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * resolve fatal
     * @see \Amasty\Checkout\Plugin\Quote\Model\Cart\CartTotalRepository::beforeGet
     *
     * @param \Magento\Framework\Api\DataObjectHelper $subject
     * @param object                                  $dataObject
     * @param array                                   $data
     * @param string                                  $interfaceName
     *
     * @return array
     */
    public function beforePopulateWithArray(
        \Magento\Framework\Api\DataObjectHelper $subject,
        $dataObject,
        array $data,
        $interfaceName
    ) {
        if ($interfaceName === TotalsInterface::class
            && $this->registry->registry(CartTotalRepositoryPlugin::REGISTRY_IGNORE_EXTENSION_ATTRIBUTES_KEY)
        ) {
            unset($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        }

        return [$dataObject, $data, $interfaceName];
    }
}
