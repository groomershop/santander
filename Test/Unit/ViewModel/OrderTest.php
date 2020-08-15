<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Test\Unit\ViewModel;

class OrderTest extends \PHPUnit\Framework\TestCase
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
        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer  =$this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyFactory = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order = new \Aurora\Santander\ViewModel\Order(
            $this->checkoutSession,
            $this->orderFactory,
            $this->urlBuilder,
            $this->scopeConfig,
            $this->serializer,
            $this->storeManager,
            $this->currencyFactory
        );
    }

    public function testGetLastOrder()
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn($order);

        $this->order->getLastOrder();
    }

    public function testGetSuccessPageUrl()
    {
        $params = [];
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('checkout/onepage/success', $params);

        $this->order->getSuccessPageUrl($params);
    }

    public function testGetInstallmentsFromConfig()
    {
        $storeId = 1;

        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->wilLReturn($store);
            
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/eraty_santander/ranges',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        $this->order->getInstallmentsFromConfig();
    }
}
