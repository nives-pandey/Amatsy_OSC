<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

/**
 * @api
 */
interface QuotePasswordsRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Checkout\Api\Data\QuotePasswordsInterface $quotePasswords
     *
     * @return \Amasty\Checkout\Api\Data\QuotePasswordsInterface
     */
    public function save(\Amasty\Checkout\Api\Data\QuotePasswordsInterface $quotePasswords);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\Checkout\Api\Data\QuotePasswordsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get by quote id
     *
     * @param int $quoteId
     *
     * @return \Amasty\Checkout\Api\Data\QuotePasswordsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByQuoteId($quoteId);

    /**
     * Delete
     *
     * @param \Amasty\Checkout\Api\Data\QuotePasswordsInterface $quotePasswords
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Checkout\Api\Data\QuotePasswordsInterface $quotePasswords);

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
