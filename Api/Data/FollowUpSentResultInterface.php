<?php

namespace Perspective\FollowUpMessages\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface FollowUpSentResultInterface extends SearchResultsInterface
{
    /**
     * Get list.
     *
     * @return array
     */
    public function getItems();

    /**
     * Set group list.
     *
     * @param array $items
     * @return $this
     */
    public function setItems(array $items);

}
