<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api\Data;

interface AdditionalFieldsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const QUOTE_ID = 'quote_id';
    const COMMENT = 'comment';
    const IS_SUBSCRIBE = 'is_subscribe';
    const IS_REGISTER = 'is_register';
    const REGISTER_DOB = 'register_dob';
    /**#@-*/

    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string|null $comment
     *
     * @return \Amasty\Checkout\Api\Data\AdditionalFieldsInterface
     */
    public function setComment($comment);

    /**
     * @return bool|int|null
     */
    public function getSubscribe();

    /**
     * @param bool|int|null $isSubscribe
     *
     * @return \Amasty\Checkout\Api\Data\AdditionalFieldsInterface
     */
    public function setSubscribe($isSubscribe);

    /**
     * @return bool|int|null
     */
    public function getRegister();

    /**
     * @param bool|int|null $isRegister
     *
     * @return \Amasty\Checkout\Api\Data\AdditionalFieldsInterface
     */
    public function setRegister($isRegister);

    /**
     * @return string|null
     */
    public function getDateOfBirth();

    /**
     * @param string|null $registerDob
     *
     * @return \Amasty\Checkout\Api\Data\AdditionalFieldsInterface
     */
    public function setDateOfBirth($registerDob);
}
