<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api\Data;

interface CustomFieldsConfigInterface
{
    /**
     * Constants defined for config values
     */
    const COUNT_OF_CUSTOM_FIELDS = 3;
    const CUSTOM_FIELD_INDEX = 1;
    const CUSTOM_FIELD_1_CODE = 'custom_field_1';
    const CUSTOM_FIELD_2_CODE = 'custom_field_2';
    const CUSTOM_FIELD_3_CODE = 'custom_field_3';
    const CUSTOM_FIELDS_ARRAY = [self::CUSTOM_FIELD_1_CODE, self::CUSTOM_FIELD_2_CODE, self::CUSTOM_FIELD_3_CODE];
}
