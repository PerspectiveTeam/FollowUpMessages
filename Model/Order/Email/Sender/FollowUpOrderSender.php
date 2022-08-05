<?php

namespace Perspective\FollowUpMessages\Model\Order\Email\Sender;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Perspective\FollowUpMessages\Exceptions\InterceptAndRefuseEmailSendingException;
use Perspective\FollowUpMessages\Model\Order\Email\Container\FollowUpOrderIdentity;
use Psr\Log\LoggerInterface;

class FollowUpOrderSender extends Sender
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var array
     */
    private $productSkuToSend = [];

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \Magento\Sales\Model\Order\Email\Container\Template $templateContainer
     * @param \Perspective\FollowUpMessages\Model\Order\Email\Container\FollowUpOrderIdentity $identityContainer
     * @param \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Template $templateContainer,
        FollowUpOrderIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        ManagerInterface $eventManager,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer
        );
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->senderBuilderFactory = $senderBuilderFactory;
        $this->logger = $logger;
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
        $this->eventManager = $eventManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Sends order email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Order|\Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send($order)
    {
        try {
            if ($this->checkAndSend($order)) {
                return true;
            }
        } catch (InterceptAndRefuseEmailSendingException $exception) {
            return false;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getProductSkuToSend(): array
    {
        return $this->productSkuToSend;
    }

    /**
     * @param array $productSkuToSend
     */
    public function setProductSkuToSend(array $productSkuToSend): void
    {
        $this->productSkuToSend = $productSkuToSend;
    }

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        if (!$this->getFollowUpMessages()) {
            throw new InterceptAndRefuseEmailSendingException(
                __('This email should not send with emtpy follow-up data')
            );
        }
        $transport = [
            'order' => $order,
            'order_id' => $order->getId(),
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'created_at_formatted' => $order->getCreatedAtFormatted(2),
            'follow_up_messages' => $this->getFollowUpMessages(),
            'order_data' => [
                'follow_up_messages' => $this->getFollowUpMessages(),
                'customer_name' => $order->getCustomerName(),
                'is_not_virtual' => $order->getIsNotVirtual(),
                'email_customer_note' => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
            ]
        ];
        $transportObject = new DataObject($transport);

        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
        $this->eventManager->dispatch(
            'email_order_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject, 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transportObject->getData());

        parent::prepareTemplate($order);
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array<string>
     */
    protected function getFollowUpMessages()
    {
        $result = [];
        $productSkuArr = $this->getProductSkuToSend();
        foreach ($productSkuArr as $item) {
            $product = $this->productRepository->get($item);
            if ($message = $product->getData('follow_up_messages')) {
                $result[$product->getId()] = $message;
            }
        }
        return $result;
    }

}
