<?php
namespace Coyuchi\GiftCardNotification\Observer;

use Coyuchi\GiftCardNotification\Data\OrderCreatedEventFactory;
use Coyuchi\GiftCardNotification\Api\Data\OrderCreatedEventInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class for Listening order created event and push order data to message queue
 *
 * Class OrderCreatedObserver
 * @package Coyuchi\GiftCardNotification\Observer
 */
class OrderCreatedObserver implements ObserverInterface
{
    const MQ_TOPIC_NAME_IMPORT = 'async.coyuchi.giftCardNotification.orderCreated';

    /**
     * @var PublisherInterface - publisher for Message Queue
     */
    protected $publisher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderCreatedEventFactory
     */
    protected $createdEventFactory;

    /**
     * OrderCreatedObserver constructor.
     *
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     * @param OrderCreatedEventFactory $createdEventFactory
     */
    public function __construct(
        PublisherInterface $publisher,
        LoggerInterface $logger,
        OrderCreatedEventFactory $createdEventFactory
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->createdEventFactory = $createdEventFactory;
    }

    /**
     * Push order data to message queue
     *
     * @param Observer $observer
     *
     * @return bool
     */
    public function execute(Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');

        /** @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        try {
            $items = $quote->getItems();
            $productsSku = [];
            foreach ($items as $item) {
                $productsSku[] = $item->getSku();
            }

            /** @var OrderCreatedEventInterface $createOrderEvent */
            $createOrderEvent = $this->createdEventFactory->create();
            $createOrderEvent->setOrderId($order->getId());
            $createOrderEvent->setProductsSku($productsSku);
            $createOrderEvent->setStoreId($order->getStoreId());

            $this->publisher->publish(self::MQ_TOPIC_NAME_IMPORT, $createOrderEvent);
        } catch (Throwable $e) {
            $this->logger->error('OrderCreatedObserver::execute error: ' . $e->getMessage());
        }
    }
}