<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Model\CheckoutConfigProvider\Gdpr;

use Amasty\Checkout\Model\Config\Source\Gdpr\CheckboxLocation;
use Amasty\Checkout\Model\ModuleEnable;
use Amasty\Gdpr\Model\Consent\DataProvider\CheckoutDataProvider;
use Magento\Framework\ObjectManagerInterface;

class ConsentsProvider
{
    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CheckboxLocation
     */
    private $checkboxLocation;

    /**
     * @var array
     */
    private $consentsConfig;

    public function __construct(
        ModuleEnable $moduleEnable,
        ObjectManagerInterface $objectManager,
        CheckboxLocation $checkboxLocation
    ) {
        $this->moduleEnable = $moduleEnable;
        $this->objectManager = $objectManager;
        $this->checkboxLocation = $checkboxLocation;
    }

    /**
     * @return array
     */
    public function getConsentsConfig(): array
    {
        if (!$this->moduleEnable->isGdprEnable()) {
            return [];
        }

        if ($this->consentsConfig === null) {
            $consentsConfig = [];
            $gdprProvider = $this->objectManager->get(CheckoutDataProvider::class);

            foreach ($this->checkboxLocation->toArray() as $location) {
                $consents = $gdprProvider->getData($location);
                if (!empty($consents['consents'])) {
                    $consentsConfig[$location] = $consents;
                }
            }

            $this->consentsConfig = $consentsConfig;
        }

        return $this->consentsConfig;
    }
}
