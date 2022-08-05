<?php


namespace Perspective\FollowUpMessages\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface;
use Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface;
use Perspective\FollowUpMessages\Helper\Config\Config;
use Perspective\FollowUpMessages\Model\FollowUpUnique;
use Perspective\FollowUpMessages\Model\Order\Email\Sender\FollowUpOrderSender;
use Perspective\FollowUpMessages\Service\ProductsAggregator;

class FollowUpSendEmailHandler
{
    /**
     * @var \Perspective\FollowUpMessages\Model\Order\Email\Sender\FollowUpOrderSender
     */
    private $followUpOrderSender;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Perspective\FollowUpMessages\Helper\Config\Config
     */
    private $followUpConfig;

    /**
     * @var \Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface
     */
    private $followUpSentRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Perspective\FollowUpMessages\Model\FollowUpUnique
     */
    private $followUpUnique;

    /**
     * @var \Perspective\FollowUpMessages\Service\ProductsAggregator
     */
    private $productsAggregator;

    /**
     * @param \Perspective\FollowUpMessages\Model\Order\Email\Sender\FollowUpOrderSender $followUpOrderSender
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface $followUpSentRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Perspective\FollowUpMessages\Helper\Config\Config $followUpConfig
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Perspective\FollowUpMessages\Model\FollowUpUnique $followUpUnique
     * @param \Perspective\FollowUpMessages\Service\ProductsAggregator $productsAggregator
     */
    public function __construct(
        FollowUpOrderSender $followUpOrderSender,
        OrderRepositoryInterface $orderRepository,
        FollowUpSentRepositoryInterface $followUpSentRepository,
        TimezoneInterface $timezone,
        Config $followUpConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FollowUpUnique $followUpUnique,
        ProductsAggregator $productsAggregator
    ) {
        $this->followUpOrderSender = $followUpOrderSender;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
        $this->followUpConfig = $followUpConfig;
        $this->followUpSentRepository = $followUpSentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->followUpUnique = $followUpUnique;
        $this->productsAggregator = $productsAggregator;
    }

    /**
     * Cronjob Description
     *
     * @return void
     */
    public function execute(): void
    {
        $this->searchCriteriaBuilder
            ->addFilter(FollowUpSentIndexInterface::PRODUCT_SENT, 0)
            ->addFilter(FollowUpSentIndexInterface::TIMESTAMP_TO_SEND, $this->timezone->date()->getTimestamp(), 'lteq')
            ->setPageSize($this->followUpConfig->getItemsInBatch());
        $searchCriteriaResult = $this->followUpSentRepository->getList($this->searchCriteriaBuilder->create());
        $orderArr = [];
        foreach ($searchCriteriaResult->getItems() as $item) {
            $orderArr[$item[FollowUpSentIndexInterface::ORDER_ID]][] = $item[FollowUpSentIndexInterface::PRODUCT_SKU];
        }
        $orderToSendUnique = array_unique($orderArr, SORT_REGULAR);
        foreach ($orderToSendUnique as $key => $value) {
            $order = $this->orderRepository->get($key);
            $products = $this->followUpUnique->getProductsAllowedToSendForCustomer($order, $value);
            $productsToMark = $products;
            $products = $this->productsAggregator->execute($products);
            $this->followUpOrderSender->setProductSkuToSend($products);
            $result = $this->followUpOrderSender->send($order);
            if ($result) {
                $this->followUpUnique->markProductsThatHasBeenSent($order, $productsToMark);
            } else {
                //удаляем из очереди на отправку, чтобы не вешать очередь
                $this->followUpUnique->removeProductsThatHaveNotMessage($order, $productsToMark);
            }
            $this->followUpOrderSender->setProductSkuToSend([]);
        }
    }
}
