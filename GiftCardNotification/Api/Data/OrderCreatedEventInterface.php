<?php

namespace Coyuchi\GiftCardNotification\Api\Data;

/**
 * Interface OrderCreatedEventInterface
 * @package Coyuchi\GiftCardNotification\Model
 */
interface OrderCreatedEventInterface
{
    /**
     * @param int $orderId
     *
     * @return self
     */
    public function setOrderId($orderId);

    /**
     * @param string[] $productsSku
     *
     * @return self
     */
    public function setProductsSku(array $productsSku);

    /**
     * @param int $storeId
     *
     * @return self
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @return string[]
     */
    public function getProductsSku();

    /**
     * @return int
     */
    public function getStoreId();
}