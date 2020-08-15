<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Test\Unit\Plugin;

class InstallmentTest extends \PHPUnit\Framework\TestCase
{
    public $installmentFactory;
    public $json;
    public $eavAttribute;
    public $subject;
    public $plugin;

    public function setUp()
    {
        $this->installmentFactory = $this->getMockBuilder(\Aurora\Santander\ViewModel\InstallmentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $installment = $this->getMockBuilder(\Aurora\Santander\ViewModel\Installment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->installmentFactory->expects($this->any())
            ->method('create')
            ->willReturn($installment);

        $this->json  =$this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavAttribute = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->getMockBuilder(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->result = $this->getMockBuilder(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new \Aurora\Santander\Plugin\Installment(
            $this->installmentFactory,
            $this->json,
            $this->eavAttribute
        );
    }

    public function testAfterGetJsonConfig()
    {
        $config = [];
        $result = $this->result->getJsonConfig();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurable = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class)
            ->setMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($configurable);

        $products = new \ArrayIterator(new \ArrayIterator([$product, $product]));

        $configurable->expects($this->once())
            ->method('getUsedProducts')
            ->willReturn($products);

        $this->json->expects($this->once())
            ->method('unserialize')
            ->with($result)
            ->willReturn($config);

        $this->plugin->afterGetJsonConfig($this->subject, $result);
    }
}
