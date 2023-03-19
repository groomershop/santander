<?php

/**
 * @copyright Copyright (c) 2023 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Test\Unit\Block;

use Aurora\Santander\Block\Form\Santander;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\TestCase;

class SantanderTest extends TestCase
{
    /**
     * @var Santander
     */
    private Santander $testClass;

    /**
     * @var MockObject|Item
     */
    private MockObject|Item $mockItem;

    /**
     * @var MockObject|Context
     */
    private MockObject|Context $mockContext;

    /**
     * @var array|array[]
     */
    private array $testCasesCalculateItemPrice = [
        [
            'price' => 50,
            'discount' => 50,
            'qty' => 5,
            'expected' => 200.00,
        ],
        [
            'price' => 50,
            'discount' => 10,
            'qty' => 1,
            'expected' => 40.00,
        ],
        [
            'price' => 20,
            'discount' => 1,
            'qty' => 10,
            'expected' => 199.00,
        ],
    ];

    public function setUp(): void
    {
        $this->mockContext = $this
            ->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testClass = new Santander($this->mockContext);
    }

    public function testCalculateItemPrice(): void
    {
        foreach ($this->testCasesCalculateItemPrice as $testCase) {
            $this->mockItem = $this->getMockBuilder(Item::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getPriceInclTax', 'getDiscountAmount', 'getQtyOrdered'])
                ->getMock();

            $this->mockItem
                ->expects($this->once())
                ->method('getPriceInclTax')
                ->willReturn($testCase['price']);

            $this->mockItem
                ->expects($this->once())
                ->method('getDiscountAmount')
                ->willReturn($testCase['discount']);

            $this->mockItem
                ->expects($this->once())
                ->method('getQtyOrdered')
                ->willReturn($testCase['qty']);

            $result = $this->testClass->calculateItemPrice($this->mockItem);
            $this->assertSame($testCase['expected'], $result);
        }
    }
}
