<?php

namespace Perspective\FollowUpMessages\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface;
use Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex as ResourceModel;

class FollowUpSentIndex extends AbstractExtensibleModel implements FollowUpSentIndexInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_followup_sent_index_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return parent::getId();
    }

    /**
     * @inheritDoc
     */
    public function setId($value)
    {
        parent::setId($value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        return $this->getData(self::CUSTOMER_ID) === null ? null
            : (int)$this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(?int $customerId): void
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getProductSku(): ?string
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setProductSku(?string $productSku): void
    {
        $this->setData(self::PRODUCT_SKU, $productSku);
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(): ?int
    {
        return $this->getData(self::TIMESTAMP) === null ? null
            : (int)$this->getData(self::TIMESTAMP);
    }

    /**
     * @inheritDoc
     */
    public function setTimestamp(?int $timestamp): void
    {
        $this->setData(self::TIMESTAMP, $timestamp);
    }
    /**
     * @inheritDoc
     */
    public function getProductSent(): ?bool
    {
        return $this->getData(self::PRODUCT_SENT) === null ? null
            : (int)$this->getData(self::PRODUCT_SENT);
    }

    /**
     * @inheritDoc
     */
    public function setProductSent(?bool $sent): void
    {
        $this->setData(self::PRODUCT_SENT, $sent);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): ?int
    {
        return $this->getData(self::ORDER_ID) === null ? null
            : (int)$this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(?int $orderId): void
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getTimestampToSend(): ?int
    {
        return $this->getData(self::TIMESTAMP_TO_SEND) === null ? null
            : (int)$this->getData(self::TIMESTAMP_TO_SEND);
    }

    /**
     * @inheritDoc
     */
    public function setTimestampToSend(?int $timestampToSend): void
    {
        $this->setData(self::TIMESTAMP_TO_SEND, $timestampToSend);
    }
}
