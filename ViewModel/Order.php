<?php
/**
 * @copyright Copyright (c) 2024 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\ViewModel;

use Exception;
use InvalidArgumentException;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Order implements ArgumentInterface
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @param Session $_checkoutSession
     * @param OrderFactory $orderFactory
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Session $_checkoutSession,
        OrderFactory $orderFactory,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory
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
     *
     * @return MagentoOrder
     */
    public function getLastOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * Get checkout success page URL
     *
     * @param array $params
     * @return string
     */
    public function getSuccessPageUrl($params)
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success', $params);
    }

    /**
     * Retrieved URL for save order page
     *
     * @param array $params
     * @return string
     */
    public function getSaveOrderPageUrl(array $params)
    {
        return $this->urlBuilder->getUrl('santander/eraty/saveorder', $params);
    }

    /**
     * Get installment ranges from config table
     *
     * @return array|bool|float|int|string|null
     * @throws NoSuchEntityException
     */
    public function getInstallmentsFromConfig()
    {
        $rates = $this->scopeConfig->getValue(
            'payment/eraty_santander/ranges',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        try {
            return $this->serializer->unserialize($rates);
        } catch (InvalidArgumentException $exception) {
            return null;
        }
    }

    /**
     * Retrieved price in PLN
     *
     * @param float $price
     * @param string $currencyCode
     * @return float
     * @throws NoSuchEntityException
     */
    public function getPricePLN(float $price, string $currencyCode)
    {
        $store = $this->storeManager->getStore();
        /* @phpstan-ignore-next-line */
        $currency = $this->currencyFactory->create()->load($currencyCode);
        $avaiableCurrencies = $store->getAvailableCurrencyCodes();
        try {
            if (in_array('PLN', $avaiableCurrencies)) {
                $price = $currency->convert($price, 'PLN');
            }
            // phpcs:ignore
        } catch (Exception $e) {
        }

        return $price;
    }
}
