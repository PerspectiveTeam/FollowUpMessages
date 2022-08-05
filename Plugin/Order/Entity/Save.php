<?php

namespace Perspective\FollowUpMessages\Plugin\Order\Entity;

use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order;

class Save
{
    /**
     * @var \Perspective\FollowUpMessages\Model\Order\Scheduler\SchedulerManager
     */
    private $manager;

    /**
     * @var \Perspective\FollowUpMessages\Helper\Logger
     */
    private $logger;


    /**
     * @param \Perspective\FollowUpMessages\Helper\Logger $logger
     */
    public function __construct(
        \Perspective\FollowUpMessages\Model\Order\Scheduler\SchedulerManager $manager,
        \Perspective\FollowUpMessages\Helper\Logger $logger
    ) {

        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function afterSave(
        OrderResource $resource,
        $result,
        $order
    ) {
        $this->checkStatusChanges($order);

        return $result;
    }

    /**
     * Check order status changes after save.
     *
     * @param Order $order
     * @return $this
     */
    protected function checkStatusChanges(Order $order)
    {
        if ($order->dataHasChangedFor('status'))
            try {
                $this->manager->schedule($order);
            } catch (\Exception $e) {
                $this->logger->error(
                    __(
                        'Could not send order status notification. Order: %1, Error: %2',
                        $order->getId(),
                        $e->getMessage()
                    ),
                    [$e]
                );
            }

        return $this;
    }
}
