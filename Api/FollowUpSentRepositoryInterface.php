<?php

namespace Perspective\FollowUpMessages\Api;

interface FollowUpSentRepositoryInterface
{
    /**
     * @param \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface $item
     * @return \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface
     */
    public function save(\Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface $item);

    /**
     * @param \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface $item
     * @return \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface
     */
    public function delete(\Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface $item);

    /**
     * Retrieve items matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Perspective\FollowUpMessages\Api\Data\FollowUpSentResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @return \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface
     */
    public function getEmptyModel();
}
