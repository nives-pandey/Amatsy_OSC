<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\DeliveryInformationManagement;
use Amasty\Checkout\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DeliveryInformationManagementTest
 *
 * @see DeliveryInformationManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DeliveryInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     *  @covers DeliveryInformationManagement::update
     */
    public function testUpdate()
    {
        $delivery = $this->createMock(\Amasty\Checkout\Model\Delivery::class);
        $deliveryObject = $this->getObjectManager()->getObject(\Amasty\Checkout\Model\Delivery::class);
        $deliveryResource = $this->createMock(\Amasty\Checkout\Model\ResourceModel\Delivery::class);

        $delivery->expects($this->any())->method('findByQuoteId')->willReturn($deliveryObject);
        $deliveryResource->expects($this->once())->method('save');
        $deliveryResource->expects($this->once())->method('delete');

        $model = $this->getObjectManager()->getObject(
            DeliveryInformationManagement::class,
            [
                'deliveryResource' => $deliveryResource,
                'delivery' => $delivery
            ]
        );

        $this->assertTrue($model->update(1, '15', 1, 'test'));

        $deliveryObject->setId(5);
        $this->assertTrue($model->update(1, null, null, null));
    }
}