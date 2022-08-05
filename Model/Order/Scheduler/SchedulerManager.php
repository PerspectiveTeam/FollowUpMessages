<?php

namespace Perspective\FollowUpMessages\Model\Order\Scheduler;

use DateInterval;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface;
use Perspective\FollowUpMessages\Helper\Config\Config;
use Magento\Sales\Model\Order;

class SchedulerManager
{
    /**
     * @var \Perspective\FollowUpMessages\Helper\Config\Config
     */
    private $followUpConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface
     */
    private $followUpSentRepository;

    /**
     * @param \Perspective\FollowUpMessages\Helper\Config\Config $followUpConfig
     * @param \Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface $followUpSentRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime
     */
    public function __construct(
        Config $followUpConfig,
        FollowUpSentRepositoryInterface $followUpSentRepository,
        TimezoneInterface $dateTime
    ) {
        $this->followUpConfig = $followUpConfig;
        $this->dateTime = $dateTime;
        $this->followUpSentRepository = $followUpSentRepository;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function schedule(Order $order)
    {
        if (!$this->followUpConfig->isEnabled()) {
            return;
        }
        if ($order->getStatus() === $this->followUpConfig->getStatusToAttach()) {
            foreach ($order->getAllItems() as $item) {
                $model = $this->followUpSentRepository->getEmptyModel();
                $timeToPostpone = $this->followUpConfig->getTimeToSendMessage();
                $model->setOrderId($order->getId());
                $model->setTimestampToSend(
                    $this->dateTime
                        ->date()
                        ->add(
                            new DateInterval('PT' . $timeToPostpone . 'M')
                        )->getTimestamp()
                );
                $model->setCustomerId($order->getCustomerId());
                $model->setProductSku($item->getSku());
                try {
                    $this->followUpSentRepository->save($model);
                } catch (\Exception $e) {
                    // В любом случае пропускаем, так как в админке, можно выставить статус на pending
                    // и заказ ДОЛЖЕН быть сохранен в любом виде
                }
            }
        }
    }
}
