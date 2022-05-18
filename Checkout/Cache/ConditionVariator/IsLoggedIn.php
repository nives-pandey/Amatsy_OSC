<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Cache\ConditionVariator;

use Amasty\Checkout\Api\CacheKeyPartProviderInterface;

/**
 * Add cache variation for logged customer and guest
 */
class IsLoggedIn implements CacheKeyPartProviderInterface
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    public function __construct(\Magento\Framework\App\Http\Context $httpContext)
    {
        $this->httpContext = $httpContext;
    }

    /**
     * @return string
     */
    public function getKeyPart()
    {
        if ($this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
            return 'logged';
        }

        return 'guest';
    }
}
