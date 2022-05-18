<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\AdditionalFieldsManagementInterface;
use Amasty\Checkout\Api\Data\AdditionalFieldsInterface;
use Amasty\Checkout\Model\AdditionalFieldsFactory;

/**
 * Class AdditionalFieldsManagement
 */
class AdditionalFieldsManagement implements AdditionalFieldsManagementInterface
{
    /**
     * @var ResourceModel\AdditionalFields
     */
    private $fieldsResource;

    /**
     * @var AdditionalFieldsFactory
     */
    private $fieldsFactory;

    /**
     * @var AdditionalFields[]
     */
    protected $storage = [];

    public function __construct(
        ResourceModel\AdditionalFields $fieldsResource,
        AdditionalFieldsFactory $fieldsFactory
    ) {
        $this->fieldsResource = $fieldsResource;
        $this->fieldsFactory = $fieldsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save($cartId, $fields)
    {
        $model = $this->getByQuoteId($cartId)->addData($fields->getData());
        $this->fieldsResource->save($model);
        $this->storage[$cartId] = $model;
        return true;
    }

    /**
     * @param int $quoteId
     *
     * @return AdditionalFields
     */
    public function getByQuoteId($quoteId)
    {
        if (!isset($this->storage[$quoteId])) {
            /** @var AdditionalFields $fields */
            $fields = $this->fieldsFactory->create();
            $this->fieldsResource->load($fields, $quoteId, AdditionalFieldsInterface::QUOTE_ID);
            $fields->setQuoteId($quoteId);
            $this->storage[$quoteId] = $fields;
        }

        return $this->storage[$quoteId];
    }
}
