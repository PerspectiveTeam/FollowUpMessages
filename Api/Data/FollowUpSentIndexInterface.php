<?php

namespace Perspective\FollowUpMessages\Api\Data;

interface FollowUpSentIndexInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

   public const CUSTOMER_ID = "customer_id";

    public const PRODUCT_SKU = "product_sku";

    public const TIMESTAMP = "timestamp";

    public const PRODUCT_SENT = "product_sent";

    public const ORDER_ID = "order_id";

    public const TIMESTAMP_TO_SEND = "timestamp_to_send";


    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     * @return $this
     */
    public function setId($value);
    /**
     * Getter for CustomerId.
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * Setter for CustomerId.
     *
     * @param int|null $customerId
     *
     * @return void
     */
    public function setCustomerId(?int $customerId): void;

    /**
     * Getter for ProductSku.
     *
     * @return string|null
     */
    public function getProductSku(): ?string;

    /**
     * Setter for ProductSku.
     *
     * @param string|null $productSku
     *
     * @return void
     */
    public function setProductSku(?string $productSku): void;

    /**
     * Getter for Timestamp.
     *
     * @return int|null
     */
    public function getTimestamp(): ?int;

    /**
     * Setter for Timestamp.
     *
     * @param int|null $timestamp
     *
     * @return void
     */
    public function setTimestamp(?int $timestamp): void;

    /**
     * Getter for Product Sent Flag.
     *
     * @return bool|null
     */
    public function getProductSent(): ?bool;

    /**
     * Setter for Product Sent Flag.
     *
     * @param int|null $timestamp
     *
     * @return void
     */
    public function setProductSent(?bool $sent): void;

    /**
     * Getter for OrderId.
     *
     * @return int|null
     */
    public function getOrderId(): ?int;

    /**
     * Setter for OrderId.
     *
     * @param int|null $orderId
     *
     * @return void
     */
    public function setOrderId(?int $orderId): void;

    /**
     * Getter for TimestampToSend.
     *
     * @return int|null
     */
    public function getTimestampToSend(): ?int;

    /**
     * Setter for TimestampToSend.
     *
     * @param int|null $timestampToSend
     *
     * @return void
     */
    public function setTimestampToSend(?int $timestampToSend): void;

}
