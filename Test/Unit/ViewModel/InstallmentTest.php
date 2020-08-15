<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Test\Unit\ViewModel;

/**
 * Installment
 */
class InstallmentTest extends \PHPUnit\Framework\TestCase
{
    public $checkoutSession;
    public $orderFactory;
    public $urlBuilder;
    public $scopeConfig;
    public $serializer;
    public $storeManager;
    public $order;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->priceHelper = $this->getMockBuilder(\Magento\Framework\Pricing\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->dataHelper = $this->getMockBuilder(\Aurora\Santander\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cart = $this->getMockBuilder(\Magento\Checkout\Helper\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository  =$this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->currencyFactory = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
         
        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock(); 
              
        $this->abstractDb = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute', '_construct'])
            ->getMock();

        $this->attr = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->frontendAttr = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend ::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address ::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAmount'])
            ->getMock();
            
        $this->installment = new \Aurora\Santander\ViewModel\Installment(
            $this->registry,
            $this->priceHelper,
            $this->dataHelper,
            $this->cart,
            $this->productRepository,
            $this->currencyFactory,
            $this->storeManager,
            $this->logger
        );
    }

    public function testCalculateInstallment()
    {
        $product = $this->product;
        $attribute = '10 x 2%';
        $product->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDb);

        $this->abstractDb->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attr);
        
        $this->attr->expects($this->once())
            ->method('getFrontend')
            ->willReturn($this->frontendAttr);
        
        $this->frontendAttr->expects($this->once())
            ->method('getValue')
            ->willReturn($attribute); 

        $this->installment->calculateInstallment($product);
        $this->assertSame($this->installment->qty, 10);
        $this->assertSame($this->installment->percent, 2.0);
    }

    public function testGetFinalPrices()
    {
        $products = [$this->product];
        $attribute = '10 x 2%';
        $price = 100;
        $shipping = 10;
        $qty = 1;

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->once())
            ->method('getItems')
            ->willReturn($products);

        $this->productRepository->expects($this->any())
            ->method('getById')
            ->with(null)
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($price);

        $this->product->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDb);

        $this->abstractDb->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attr);
        
        $this->attr->expects($this->once())
            ->method('getFrontend')
            ->willReturn($this->frontendAttr);
        
        $this->frontendAttr->expects($this->once())
            ->method('getValue')
            ->willReturn($attribute);

        $this->product->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);

        $this->quote->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->address);

        $this->address->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn($shipping);

        $result = $this->installment->getFinalPrices($products);

        $this->assertSame(110, $result);
    }
}
