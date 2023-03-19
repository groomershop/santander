<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Test\Unit\ViewModel;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Aurora\Santander\ViewModel\Order;

class OrderTest extends TestCase
{
    /**
     * @var MockObject|Session
     */
    public MockObject|Session $checkoutSession;

    /**
     * @var MockObject|OrderFactory
     */
    public MockObject|OrderFactory $orderFactory;

    /**
     * @var MockObject|UrlInterface
     */
    public MockObject|UrlInterface $urlBuilder;

    /**
     * @var MockObject|ScopeInterface
     */
    public MockObject|ScopeInterface $scopeConfig;

    /**
     * @var MockObject|SerializerInterface
     */
    public MockObject|SerializerInterface $serializer;

    /**
     * @var MockObject|StoreManagerInterface
     */
    public MockObject|StoreManagerInterface $storeManager;

    /**
     * @var MockObject|Order
     */
    public MockObject|Order $order;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order = new Order(
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
        $order = $this->getMockBuilder(MagentoOrder::class)
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

        $store = $this->getMockBuilder(StoreInterface::class)
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
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        $this->order->getInstallmentsFromConfig();
    }
}
