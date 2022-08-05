<?php

namespace Perspective\FollowUpMessages\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FollowUpSentIndex extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_followup_sent_index_resource_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('sales_order_status_followup_sent_index', 'id');
        $this->_useIsObjectNew = true;
    }
}
