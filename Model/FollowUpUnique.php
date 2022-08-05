<?php

namespace Perspective\FollowUpMessages\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface;
use Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface;
use Perspective\FollowUpMessages\Helper\Config\Config;

class FollowUpUnique
{
    /**
     * @var \Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface
     */
    private $followUpSentRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Perspective\FollowUpMessages\Helper\Config\Config
     */
    private $followUpConfig;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface $followUpSentRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Perspective\FollowUpMessages\Helper\Config\Config $followUpConfig
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        FollowUpSentRepositoryInterface $followUpSentRepository,
        TimezoneInterface $timezone,
        Config $followUpConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->followUpSentRepository = $followUpSentRepository;
        $this->timezone = $timezone;
        $this->followUpConfig = $followUpConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $productsFromBatch
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsAllowedToSendForCustomer($order, $productsFromBatch = [])
    {
        $customerId = $order->getCustomerId();
        $this->searchCriteriaBuilder
            ->addFilter(FollowUpSentIndexInterface::CUSTOMER_ID, $customerId)
            ->addFilter(FollowUpSentIndexInterface::PRODUCT_SENT, 1)
            ->addFilter(FollowUpSentIndexInterface::TIMESTAMP_TO_SEND, $this->timezone->date()->getTimestamp(), 'lteq');
        $searchCriteriaResult = $this->followUpSentRepository->getList($this->searchCriteriaBuilder->create());
        $itemsThatAlreadySent = $searchCriteriaResult->getItems();
        $itemsThatAlreadySentArray = [];
        foreach ($itemsThatAlreadySent as $item) {
            $itemsThatAlreadySentArray[] = $item[FollowUpSentIndexInterface::PRODUCT_SKU];
        }
        return array_diff($productsFromBatch, $itemsThatAlreadySentArray);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $products
     * @return void
     */
    public function markProductsThatHasBeenSent($order, $products = [])
    {
        $customerId = $order->getCustomerId();
        $this->searchCriteriaBuilder
            ->addFilter(FollowUpSentIndexInterface::CUSTOMER_ID, $customerId)
            ->addFilter(FollowUpSentIndexInterface::PRODUCT_SKU, $products, 'in');
        $searchCriteriaResult = $this->followUpSentRepository->getList($this->searchCriteriaBuilder->create());
        $itemsToMark = $searchCriteriaResult->getItems();
        foreach ($itemsToMark as $item) {
            $item[FollowUpSentIndexInterface::PRODUCT_SENT] = 1;
            $model = $this->followUpSentRepository->getEmptyModel();
            $model->addData($item);
            $this->followUpSentRepository->save($model);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $products
     * @return void
     */
    public function removeProductsThatHaveNotMessage($order, $products = [])
    {
        $customerId = $order->getCustomerId();
        $this->searchCriteriaBuilder
            ->addFilter(FollowUpSentIndexInterface::CUSTOMER_ID, $customerId)
            ->addFilter(FollowUpSentIndexInterface::PRODUCT_SKU, $products, 'in');
        $searchCriteriaResult = $this->followUpSentRepository->getList($this->searchCriteriaBuilder->create());
        $itemsToDelete = $searchCriteriaResult->getItems();
        foreach ($itemsToDelete as $item) {
            $model = $this->followUpSentRepository->getEmptyModel();
            $model->addData($item);
            $this->followUpSentRepository->delete($model);
        }
    }
}
