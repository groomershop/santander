<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Test\Unit\Model\Plugin;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Swatches\Model\Plugin\EavAttribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as BlockConfigurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ModelConfigurable;
use Aurora\Santander\ViewModel\Installment;
use Aurora\Santander\ViewModel\InstallmentFactory;
use Aurora\Santander\Plugin\Installment as AuroraInstallment;

class InstallmentTest extends TestCase
{
    /**
     * @var MockObject|InstallmentFactory
     */
    public MockObject|InstallmentFactory $installmentFactory;

    /**
     * @var MockObject|Json
     */
    public MockObject|Json $json;

    /**
     * @var MockObject|EavAttribute
     */
    public MockObject|EavAttribute $eavAttribute;

    /**
     * @var MockObject|BlockConfigurable
     */
    public MockObject|BlockConfigurable $subject;

    /**
     * @var MockObject|AuroraInstallment
     */
    public MockObject|AuroraInstallment $plugin;

    /**
     * @var MockObject|BlockConfigurable
     */
    public MockObject|BlockConfigurable $result;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->installmentFactory = $this->getMockBuilder(InstallmentFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $installment = $this->getMockBuilder(Installment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->installmentFactory->expects($this->any())
            ->method('create')
            ->willReturn($installment);

        $this->json = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavAttribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->getMockBuilder(BlockConfigurable::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->result = $this->getMockBuilder(BlockConfigurable::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new AuroraInstallment(
            $this->installmentFactory,
            $this->json,
            $this->eavAttribute
        );
    }

    public function testAfterGetJsonConfig()
    {
        $config = [];
        $result = $this->result->getJsonConfig();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurable = $this->getMockBuilder(ModelConfigurable::class)
            ->onlyMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($configurable);

        $products = new ArrayIterator(new ArrayIterator([$product, $product]));

        $configurable->expects($this->once())
            ->method('getUsedProducts')
            ->willReturn($products);

        $this->json
            ->expects($this->once())
            ->method('unserialize')
            ->with($result)
            ->willReturn($config);

        $this->plugin->afterGetJsonConfig($this->subject, $result);
    }
}
