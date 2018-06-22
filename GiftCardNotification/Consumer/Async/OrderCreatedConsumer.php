<?php

namespace Coyuchi\GiftCardNotification\Consumer\Async;

use Coyuchi\GiftCardNotification\Api\Data\OrderCreatedEventInterface;
use Coyuchi\GiftCardNotification\Service\SendGiftCardEmailService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class OrderCreatedConsumer
 * @package Coyuchi\GiftCardNotification\Consumer\Async
 *
 * Send email notification if there was e-gift card product in order
 */
class OrderCreatedConsumer
{
    const XML_PATH_GIFT_CARD_CSV = 'gift_card_notification/general/gift_card_sku_csv';
    const XML_PATH_GIFT_CARD_IS_ENABLED = 'gift_card_notification/general/enabled';

    /**
     * For getting e-gift cards sku from file
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * For getting e-gift cards sku from file
     *
     * @var File
     */
    protected $filesystem;

    /**
     * Access to config data
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Array of gift card SKUs
     *
     * @var string[]
     */
    protected $giftCardsSku = [];

    /**
     * Sender for email notification
     *
     * @var SendGiftCardEmailService
     */
    protected $giftCardEmailService;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * OrderCreatedConsumer constructor.
     *
     * @param DirectoryList $directoryList
     * @param File $filesystem
     * @param ScopeConfigInterface $scopeConfig
     * @param SendGiftCardEmailService $giftCardEmailService
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryList $directoryList,
        File $filesystem,
        ScopeConfigInterface $scopeConfig,
        SendGiftCardEmailService $giftCardEmailService,
        LoggerInterface $logger
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
        $this->giftCardEmailService = $giftCardEmailService;
        $this->logger = $logger;
    }

    /**
     * Process messages from MessageQueue system and send email notification for orders with e-gift card sku
     *
     * @param OrderCreatedEventInterface $event
     *
     * @return boolean
     */
    public function processMessage(OrderCreatedEventInterface $event)
    {
        if ($this->isEnabled()
            && $this->hasGiftCardSku($event->getProductsSku())
        ) {
            try {
                $this->giftCardEmailService->execute($event);
            } catch (Throwable $e) {
                $this->logger->error('e-Gift card send email error: '.$e->getMessage());
            }
        }

        return true;
    }

    /**
     * Check if feature is enabled
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_GIFT_CARD_IS_ENABLED) === 1;
    }

    /**
     * Check if order has products with gift card SKUs
     *
     * @param array $productsSku
     *
     * @return bool
     */
    protected function hasGiftCardSku($productsSku)
    {
        $giftCardsSku = $this->getGiftCardsSku();

        return !empty(array_intersect($giftCardsSku, $productsSku));
    }

    /**
     * Get gift cards sku from file or variable
     *
     * @return string[]
     */
    protected function getGiftCardsSku()
    {
        if (!empty($this->giftCardsSku)) {
            return $this->giftCardsSku;
        }

        $giftCardsSkuCsv = $this->directoryList->getPath('var')
            .'/gift_card_notification/config/'
            .$this->scopeConfig->getValue(self::XML_PATH_GIFT_CARD_CSV);
        if ($this->filesystem->isFile($giftCardsSkuCsv)) {
            $fileResource = $this->filesystem->fileOpen($giftCardsSkuCsv, 'r');
            while (false !== ($csvLine = $this->filesystem->fileGetCsv($fileResource))) {
                if (is_string($csvLine)) {
                    $this->giftCardsSku[] = $csvLine;
                } elseif (isset($csvLine[0]) && $csvLine[0]) {
                    $this->giftCardsSku[] = $csvLine[0];
                }
            }
        }

        return $this->giftCardsSku;
    }
}