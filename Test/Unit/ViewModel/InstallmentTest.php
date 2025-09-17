<?php
/**
 * @copyright Copyright (c) 2024 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Test\Unit\ViewModel;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote;
use Magento\Framework\Registry;
use Magento\Checkout\Helper\Cart;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\Currency;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Aurora\Santander\ViewModel\Installment;
use Aurora\Santander\Helper\Data as AuroraData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class InstallmentTest extends TestCase
{
    /** @var MockObject|Registry|(Registry&MockObject) */
    public MockObject|Registry $registry;

    /** @var MockObject|Data */
    public MockObject|Data $priceHelper;

    /** @var MockObject|AuroraData */
    public MockObject|AuroraData $dataHelper;

    /** @var MockObject|Cart */
    public MockObject|Cart $cart;

    /** @var MockObject|ProductRepositoryInterface */
    public MockObject|ProductRepositoryInterface $productRepository;

    /** @var MockObject|CurrencyFactory */
    public MockObject|CurrencyFactory $currencyFactory;

    /** @var MockObject|StoreManagerInterface */
    public MockObject|StoreManagerInterface $storeManager;

    /** @var MockObject|LoggerInterface */
    public MockObject|LoggerInterface $logger;

    /** @var MockObject|Product */
    public MockObject|Product $product;

    /** @var MockObject|Store */
    public MockObject|Store $store;

    /** @var MockObject|Currency */
    public MockObject|Currency $currency;

    /** @var MockObject|Quote */
    public MockObject|Quote $quote;

    /** @var MockObject|AbstractDb */
    public MockObject|AbstractDb $abstractDb;

    /** @var MockObject|AbstractAttribute */
    public MockObject|AbstractAttribute $attr;

    /** @var MockObject|AbstractFrontend */
    public MockObject|AbstractFrontend $frontendAttr;

    /** @var MockObject|Address */
    public MockObject|Address $address;

    /** @var Installment */
    public MockObject|Installment $installment;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataHelper = $this->getMockBuilder(AuroraData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->abstractDb = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute', '_construct'])
            ->getMock();

        $this->attr = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->frontendAttr = $this->getMockBuilder(AbstractFrontend ::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->address = $this->getMockBuilder(Address ::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAmount'])
            ->getMock();

        $this->installment = new Installment(
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
