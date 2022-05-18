<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Model\Quote;

use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Amasty\Checkout\Api\Data\QuoteCustomFieldsInterface;
use Amasty\Checkout\Model\QuoteCustomFields;
use Amasty\Checkout\Model\QuoteCustomFieldsFactory;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields as QuoteCustomFieldsResource;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\Collection;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\CollectionFactory;

class CustomFieldItemsProvider
{
    /**
     * @var QuoteCustomFields[]
     */
    private $itemsStorage = [];

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var QuoteCustomFieldsFactory
     */
    private $customFieldsFactory;

    /**
     * @var QuoteCustomFieldsResource
     */
    private $customFieldsResource;

    public function __construct(
        CollectionFactory $collectionFactory,
        QuoteCustomFieldsFactory $customFieldsFactory,
        QuoteCustomFieldsResource $customFieldsResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customFieldsFactory = $customFieldsFactory;
        $this->customFieldsResource = $customFieldsResource;
    }

    /**
     * @param int $quoteId
     *
     * @return QuoteCustomFields[]|QuoteCustomFieldsInterface[]
     */
    public function getItemsByQuoteId(int $quoteId): array
    {
        if (!isset($this->itemsStorage[$quoteId])) {
            $this->itemsStorage[$quoteId] = [];
            /** @var Collection $customFieldsCollection */
            $customFieldsCollection = $this->collectionFactory->create();
            $customFieldsCollection->addFieldByQuoteId($quoteId);

            foreach (CustomFieldsConfigInterface::CUSTOM_FIELDS_ARRAY as $fieldName) {
                /** @var QuoteCustomFields $item */
                $item = $customFieldsCollection->getItemByColumnValue('name', $fieldName);
                if (!$item) {

                    $item = $this->customFieldsFactory->create(
                        ['data' => ['quote_id' => $quoteId, 'name' => $fieldName]]
                    );
                }
                $item->setDataChanges(false);

                $this->itemsStorage[$quoteId][] = $item;
            }
        }

        return $this->itemsStorage[$quoteId];
    }
}
