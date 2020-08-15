<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\ViewModel;

/**
 * Order
 */
class Order implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->_checkoutSession = $_checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Get last order id from session
     * @return \Magento\Sales\Model\Order
     */
    public function getLastOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * Get checkout success page URL
     * @param array $params
     * @return string
     */
    public function getSuccessPageUrl($params)
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success', $params);
    }

    /**
     * Get installment ranges from config table
     * @return array|null
     */
    public function getInstallmentsFromConfig()
    {
        $rates = $this->scopeConfig->getValue(
            'payment/eraty_santander/ranges',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        try {
            return $this->serializer->unserialize($rates);
        } catch (\InvalidArgumentException $exception) {
            return null;
        }
    }

    public function getPricePLN($price, $currencyCode)
    {
        $store = $this->storeManager->getStore();
        $currency = $this->currencyFactory->create()->load($currencyCode);
        $avaiableCurrencies = $store->getAvailableCurrencyCodes();
        try {
            if (in_array('PLN', $avaiableCurrencies)) {
                $price = $currency->convert($price, 'PLN');
            }
        } catch (\Exception $e) {
        }
        
        return $price;
    }
}
