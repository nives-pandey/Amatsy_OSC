<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\DeliveryInformationManagementInterface;
use Amasty\Checkout\Model\ResourceModel\Delivery as DeliveryResource;
use Magento\Framework\Escaper;

/**
 * Class DeliveryInformationManagement
 */
class DeliveryInformationManagement implements DeliveryInformationManagementInterface
{
    /**
     * @var DeliveryResource
     */
    protected $deliveryResource;

    /**
     * @var Delivery
     */
    protected $delivery;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        DeliveryResource $deliveryResource,
        Delivery $delivery,
        Escaper $escaper
    ) {
        $this->deliveryResource = $deliveryResource;
        $this->delivery = $delivery;
        $this->escaper = $escaper;
    }

    /**
     * @inheritdoc
     */
    public function update($cartId, $date, $time = -1, $comment = '')
    {
        $delivery = $this->delivery->findByQuoteId($cartId);

        $delivery->addData([
            'date' => strtotime($date) ?: null,
            'time' => $time >= 0 ? $time : null,
            'comment' => ($comment) ? $this->escaper->escapeHtml($comment) : null
        ]);

        if ($delivery->getData('date') === null
            && $delivery->getData('time') === null
            && $delivery->getData('comment') === null
        ) {
            if ($delivery->getId()) {
                $this->deliveryResource->delete($delivery);
            }
        } else {
            $this->deliveryResource->save($delivery);
        }

        return true;
    }
}
