<?php

namespace Perspective\FollowUpMessages\Model\Order\Email\Container;

use Magento\Sales\Model\Order\Email\Container\IdentityInterface;

class FollowUpOrderIdentity extends \Magento\Sales\Model\Order\Email\Container\OrderIdentity implements IdentityInterface
{
    const XML_PATH_EMAIL_GUEST_TEMPLATE = 'sales_email/order/ps_email_template_follow_up_guest';
    const XML_PATH_EMAIL_TEMPLATE = 'sales_email/order/ps_email_template_follow_up';
    /**
     * Return guest template id
     *
     * @return mixed
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }
}
