<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


declare(strict_types=1);

namespace Amasty\Checkout\Plugin\Checkout\Block;

use Amasty\Checkout\Model\Quote\CheckoutInitialization;
use Magento\Checkout\Block\Onepage;
use Magento\Checkout\Model\Session;

/**
 * @since 3.0.5
 */
class OnepagePlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CheckoutInitialization
     */
    private $checkoutInitialization;

    public function __construct(
        Session $checkoutSession,
        CheckoutInitialization $checkoutInitialization
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutInitialization = $checkoutInitialization;
    }

    /**
     * Set initial quote value
     *
     * @param Onepage $subject
     */
    public function beforeGetJsLayout(Onepage $subject)
    {
        $this->checkoutInitialization->initializeShipping($this->checkoutSession->getQuote());
    }
}
