<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Model\CheckoutConfigProvider;

use Amasty\Checkout\Model\CheckoutConfigProvider\Gdpr\ConsentsProvider;
use Magento\Checkout\Model\ConfigProviderInterface;

class Gdpr implements ConfigProviderInterface
{
    const CONFIG_KEY = 'amastyOscGdprConsent';

    /**
     * @var ConsentsProvider
     */
    private $consentsProvider;

    public function __construct(
        ConsentsProvider $consentsProvider
    ) {
        $this->consentsProvider = $consentsProvider;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            static::CONFIG_KEY => $this->consentsProvider->getConsentsConfig()
        ];
    }
}
