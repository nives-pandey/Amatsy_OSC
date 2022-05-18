<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Test\Unit\Model;

use Amasty\Checkout\Model\CustomerValidator;
use Amasty\Checkout\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CustomerValidatorTest
 *
 * @see CustomerValidator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CustomerValidatorTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     *  @covers CustomerValidator::validate
     */
    public function testValidate()
    {
        $eavData = $this->createMock(\Magento\Eav\Model\Validator\Attribute\Data::class);
        $model = $this->getObjectManager()->getObject(CustomerValidator::class, ['eavData' => $eavData]);
        $customer = $this->getObjectManager()->getObject(\Magento\Customer\Model\Customer::class);

        $eavData->expects($this->any())->method('isValid')->will($this->onConsecutiveCalls(true, false));
        $eavData->expects($this->any())->method('getMessages')->willReturn([]);

        $this->assertTrue($model->validate($customer));
        $this->assertFalse($model->validate($customer));
    }
}