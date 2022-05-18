<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Order;

use Amasty\Checkout\Model\Config;
use Magento\Sales\Block\Items\AbstractItems;

/**
 * Class Delivery
 */
class Delivery
{
    /**
     * @var Config
     */
    private $checkoutConfig;

    public function __construct(
        Config $checkoutConfig
    ) {
        $this->checkoutConfig = $checkoutConfig;
    }

    /**
     * @param AbstractItems $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(
        AbstractItems $subject,
        $result
    ) {
        if (!$this->checkoutConfig->isEnabled()) {
            return $result;
        }
        foreach ($subject->getLayout()->getUpdate()->getHandles() as $handle) {
            if (substr($handle, 0, 12) !== 'sales_email_') {
                return $result;
            }
            /** @var  \Magento\Sales\Model\Order $order */
            $order = $subject->getOrder();
            if (!$order || !$order->getId()) {
                return $result;
            }

            $deliveryBlock = $subject->getLayout()
                ->createBlock(
                    \Amasty\Checkout\Block\Sales\Order\Email\Delivery::class,
                    'amcheckout.delivery',
                    [
                        'data' => [
                            'order_id' => $order->getId()
                        ]
                    ]
                );

            $result = $deliveryBlock->toHtml() . $result;

            if ($this->checkoutConfig->getAdditionalOptions('comment')) {
                $commentsBlock = $subject->getLayout()
                    ->createBlock(
                        \Amasty\Checkout\Block\Sales\Order\Email\Comments::class,
                        'amcheckout.comments',
                        [
                            'data' => [
                                'order_entity' => $order
                            ]
                        ]
                    );

                $result = $commentsBlock->toHtml() . $result;
            }
        }

        return $result;
    }
}
