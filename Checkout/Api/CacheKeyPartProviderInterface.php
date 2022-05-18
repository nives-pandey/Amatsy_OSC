<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

/**
 * Cache variator interface.
 * Return cache key/identifier part.
 * @since 3.0.0
 */
interface CacheKeyPartProviderInterface
{
    /**
     * @return string
     */
    public function getKeyPart();
}
