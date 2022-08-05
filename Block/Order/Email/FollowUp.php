<?php

namespace Perspective\FollowUpMessages\Block\Order\Email;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class FollowUp extends Template
{

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|mixed
     */
    private $orderRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     * @param array $data
     * @param \Magento\Sales\Api\OrderRepositoryInterface|null $orderRepository
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null
    ) {
        $this->orderRepository = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * Returns order.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So order is loaded by order_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return OrderInterface|null
     * @since 102.1.0
     */
    public function getOrder()
    {
        $order = $this->getData('order');

        if ($order !== null) {
            return $order;
        }
        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }

    /**
     * Данные идут с @see \Perspective\FollowUpMessages\Model\Order\Email\Sender\FollowUpOrderSender::prepareTemplate
     * @return array|mixed|null
     */
    public function getFollowUpMessages()
    {
        return $this->getData('follow_up_messages');
    }
}
