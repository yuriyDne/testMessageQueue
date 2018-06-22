<?php
namespace Coyuchi\GiftCardNotification\Service;

use Coyuchi\GiftCardNotification\Api\Data\OrderCreatedEventInterface;
use Magento\Backend\Helper\Data as BackendData;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Email service for sending backend link for order with e-gift card product
 *
 * Class SendGiftCardEmailService
 * @package Coyuchi\GiftCardNotification\Service
 */
class SendGiftCardEmailService
{
    const XML_PATH_GIFT_CARD_EMAIL_TEMPLATE = 'gift_card_notification/general/email_template';
    const XML_PATH_GIFT_CARD_EMAIL_FROM_ID = 'gift_card_notification/general/email_from';
    const XML_PATH_GIFT_CARD_NOTIFY_EMAIL = 'gift_card_notification/general/email';

    /**
     *  Access to config data
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Builder for email transfer
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;
    
    /**
     * Backend helper for link creation
     *
     * @var BackendData
     */
    protected $backEndData;

    /**
     * SendGiftCardEmailService constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param BackendData $backEndData
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        BackendData $backEndData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->backEndData = $backEndData;
    }

    /**
     * Send backend link for order with e-gift card product
     *
     * @param OrderCreatedEventInterface $event
     *
     * @return bool
     */
    public function execute(OrderCreatedEventInterface $event)
    {
        $backendOrderLink = $this->backEndData->getUrl(
            'sales/order/view',
            [
                'order_id' => $event->getOrderId()
            ]
        );

        $templateName = $this->scopeConfig->getValue(self::XML_PATH_GIFT_CARD_EMAIL_TEMPLATE);
        $emailFrom = $this->getEmailFrom();
        $notifyEmail = explode(',', $this->scopeConfig->getValue(self::XML_PATH_GIFT_CARD_NOTIFY_EMAIL));

        $this->transportBuilder->setTemplateIdentifier($templateName)
            ->setTemplateOptions([
                'store' => $event->getStoreId(),
                'area' => Area::AREA_FRONTEND,
            ])->setTemplateVars([
                'backend_order_link' => $backendOrderLink,
            ])->setFrom([
                'name' => 'eGiftCard order',
                'email' =>  $emailFrom
            ])->addTo(
                $notifyEmail
            );

        $transport = $this->transportBuilder->getTransport();

        $transport->sendMessage();

        return true;
    }

    /**
     * Return from email address
     *
     * @return string
     */
    protected function getEmailFrom()
    {
        $emailFromId = $this->scopeConfig->getValue(self::XML_PATH_GIFT_CARD_EMAIL_FROM_ID);
        return $this->scopeConfig->getValue("trans_email/ident_{$emailFromId}/email");
    }

}