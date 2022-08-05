<?php

namespace Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Perspective\FollowUpMessages\Model\FollowUpSentIndex as Model;
use Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_followup_sent_index_collection';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
