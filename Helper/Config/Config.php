<?php

namespace Perspective\FollowUpMessages\Helper\Config;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const ENABLED = 'followup/general/enabled';

    const STATUS = 'followup/general/status';

    const TIME_TO_SEND_MESSAGE = 'followup/general/time_to_send_message';

    const ITEMS_IN_BATCH = 'followup/general/items_in_batch';

    /**
     * Is notifications enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return !!$this->scopeConfig->getValue(self::ENABLED);
    }

    /**
     * @return mixed
     */
    public function getStatusToAttach()
    {
        return $this->scopeConfig->getValue(self::STATUS);
    }

    /**
     * @return mixed
     */
    public function getTimeToSendMessage()
    {
        return $this->scopeConfig->getValue(self::TIME_TO_SEND_MESSAGE);
    }
    /**
     * @return mixed
     */
    public function getItemsInBatch()
    {
        return $this->scopeConfig->getValue(self::ITEMS_IN_BATCH);
    }

}
