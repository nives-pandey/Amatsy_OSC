<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\Data\QuotePasswordsInterface;
use Amasty\Checkout\Api\QuotePasswordsRepositoryInterface;
use Amasty\Checkout\Model\QuotePasswordsFactory;
use Amasty\Checkout\Model\ResourceModel\QuotePasswords as QuotePasswordsResource;
use Amasty\Checkout\Model\ResourceModel\QuotePasswords\CollectionFactory;
use Amasty\Checkout\Model\ResourceModel\QuotePasswords\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * Class QuotePasswordsRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuotePasswordsRepository implements QuotePasswordsRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var QuotePasswordsFactory
     */
    private $quotePasswordsFactory;

    /**
     * @var QuotePasswordsResource
     */
    private $quotePasswordsResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $quotePasswordss;

    /**
     * @var CollectionFactory
     */
    private $quotePasswordsCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        QuotePasswordsFactory $quotePasswordsFactory,
        QuotePasswordsResource $quotePasswordsResource,
        CollectionFactory $quotePasswordsCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->quotePasswordsFactory = $quotePasswordsFactory;
        $this->quotePasswordsResource = $quotePasswordsResource;
        $this->quotePasswordsCollectionFactory = $quotePasswordsCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(QuotePasswordsInterface $quotePasswords)
    {
        try {
            if ($quotePasswords->getEntityId()) {
                $quotePasswords = $this->getById($quotePasswords->getEntityId())->addData($quotePasswords->getData());
            }
            $this->quotePasswordsResource->save($quotePasswords);
            unset($this->quotePasswordss[$quotePasswords->getEntityId()]);
        } catch (\Exception $e) {
            if ($quotePasswords->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save quotePasswords with ID %1. Error: %2',
                        [$quotePasswords->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new quotePasswords. Error: %1', $e->getMessage()));
        }

        return $quotePasswords;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->quotePasswordss[$entityId])) {
            /** @var \Amasty\Checkout\Model\QuotePasswords $quotePasswords */
            $quotePasswords = $this->quotePasswordsFactory->create();
            $this->quotePasswordsResource->load($quotePasswords, $entityId);
            if (!$quotePasswords->getEntityId()) {
                throw new NoSuchEntityException(__('QuotePasswords with specified ID "%1" not found.', $entityId));
            }
            $this->quotePasswordss[$entityId] = $quotePasswords;
        }

        return $this->quotePasswordss[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getByQuoteId($quoteId)
    {
        if (!isset($this->quotePasswordss[$quoteId])) {
            /** @var \Amasty\Checkout\Model\QuotePasswords $quotePasswords */
            $quotePasswords = $this->quotePasswordsFactory->create();
            $this->quotePasswordsResource->load($quotePasswords, $quoteId, QuotePasswordsInterface::QUOTE_ID);
            if (!$quotePasswords->getEntityId()) {
                throw new NoSuchEntityException(__('QuotePasswords with specified Quote ID "%1" not found.', $quoteId));
            }
            $this->quotePasswordss[$quoteId] = $quotePasswords;
        }

        return $this->quotePasswordss[$quoteId];
    }

    /**
     * @inheritdoc
     */
    public function delete(QuotePasswordsInterface $quotePasswords)
    {
        try {
            $this->quotePasswordsResource->delete($quotePasswords);
            unset($this->quotePasswordss[$quotePasswords->getEntityId()]);
        } catch (\Exception $e) {
            if ($quotePasswords->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove quotePasswords with ID %1. Error: %2',
                        [$quotePasswords->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove quotePasswords. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $quotePasswordsModel = $this->getById($entityId);
        $this->delete($quotePasswordsModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Checkout\Model\ResourceModel\QuotePasswords\Collection $quotePasswordsCollection */
        $quotePasswordsCollection = $this->quotePasswordsCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $quotePasswordsCollection);
        }
        
        $searchResults->setTotalCount($quotePasswordsCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $quotePasswordsCollection);
        }
        
        $quotePasswordsCollection->setCurPage($searchCriteria->getCurrentPage());
        $quotePasswordsCollection->setPageSize($searchCriteria->getPageSize());
        
        $quotePasswordss = [];
        /** @var QuotePasswordsInterface $quotePasswords */
        foreach ($quotePasswordsCollection->getItems() as $quotePasswords) {
            $quotePasswordss[] = $this->getById($quotePasswords->getEntityId());
        }
        
        $searchResults->setItems($quotePasswordss);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $quotePasswordsCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $quotePasswordsCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $quotePasswordsCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $quotePasswordsCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $quotePasswordsCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $quotePasswordsCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
