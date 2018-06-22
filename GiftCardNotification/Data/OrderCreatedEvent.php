<?php

namespace Coyuchi\GiftCardNotification\Data;

use Coyuchi\GiftCardNotification\Api\Data\OrderCreatedEventInterface;

/**
 * Data object for encode/decode data from/to message queue
 *
 * Class OrderCreatedEvent
 * @package Coyuchi\GiftCardNotification\Model
 */
class OrderCreatedEvent implements OrderCreatedEventInterface
{
    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var string[]
     */
    protected $productsSku;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setProductsSku(array $productsSku)
    {
        $this->productsSku = $productsSku;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->storeId = (int) $storeId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @inheritdoc
     */
    public function getProductsSku()
    {
        return $this->productsSku;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->storeId;
    }
}