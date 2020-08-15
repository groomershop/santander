<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\ViewModel;

/**
 * Installment
 */
class Installment implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const MIN_PRODUCT_PRICE = 'santander_configuration/calc_button_configuration/min_product_price';
    const MAX_PRODUCT_PRICE = 'santander_configuration/calc_button_configuration/max_product_price';
    const MIN_ORDER_TOTAL = 'payment/eraty_santander/min_order_total';
    const MAX_ORDER_TOTAL = 'payment/eraty_santander/max_order_total';
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Aurora\Santander\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var int
     */
    public $qty;

    /**
     * @var float
     */
    public $percent;
    
    /**
     * @var float
     */
    public $price;

   /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Aurora\Santander\Helper\Data $dataHelper,
     * @param \Magento\Checkout\Helper\Cart $cart,
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Aurora\Santander\Helper\Data $dataHelper,
        \Magento\Checkout\Helper\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->priceHelper = $priceHelper;
        $this->dataHelper = $dataHelper;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Get product model
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * Calculate installment qty and value
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function calculateInstallment($product)
    {
        $this->qty = null;
        $this->percent = null;
        $this->price = $product->getFinalPrice();
        $attribute = $product->getResource()->getAttribute(\Aurora\Santander\Model\Santander::ATTRIBUTE_CODE);

        if ($attribute) {
            $label = $attribute->getFrontend()->getValue($product);

            preg_match('/(\d+)\s*x\s*(.*)\s*\%/', $label, $matches, PREG_OFFSET_CAPTURE);

            if (isset($matches[1][0]) && isset($matches[2][0])) {
                $this->qty = (int)$matches[1][0];
                $this->percent = (float)$matches[2][0];
            }
        }
    }

    /**
     * Get unit installment price
     * @return float
     */
    public function getPrice()
    {
        if ($this->percent > 0) {
            return $this->priceHelper->currency(($this->price * (1 + ($this->percent/100))) / $this->qty, true, false);
        }

        return $this->priceHelper->currency($this->price / $this->qty, true, false);
    }

    /**
     * @param $price
     * @return boolean
     */
    public function isAvailable($price = null)
    {
        $minProductPrice = $this->dataHelper->getConfigValue(self::MIN_PRODUCT_PRICE);
        $maxProductPrice = $this->dataHelper->getConfigValue(self::MAX_PRODUCT_PRICE);
        return $this->checkPrices($price, $minProductPrice, $maxProductPrice);
    }

    /**
     * @param $price
     * @return boolean
     */
    public function isAvailableInCart($price = null)
    {
        $minProductPrice = $this->dataHelper->getConfigValue(self::MIN_ORDER_TOTAL);
        $maxProductPrice = $this->dataHelper->getConfigValue(self::MAX_ORDER_TOTAL);
        return $this->checkPrices($price, $minProductPrice, $maxProductPrice);
    }

    /**
     * @param $price
     * @param $minProductPrice
     * @param $maxProductPrice
     * @return boolean
     */
    public function checkPrices($price, $minProductPrice, $maxProductPrice)
    {
        $this->price = $price ? $price : $this->price;
        if ($this->price > $minProductPrice && $this->price < $maxProductPrice) {
            return true;
        } elseif ($this->price > $minProductPrice && !isset($maxProductPrice)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return boolean
     */
    public function getFinalPrices()
    {
        $products = $this->getCartProducts();
        $finalPrice = 0;
        foreach ($products as $product) {
            $id = $product->getProductId();
            $productData = $this->productRepository->getById($id);
            $this->calculateInstallment($productData);
            if ($this->qty != null && $this->percent != null) {
                $finalPrice += $this->price * $product->getQty();
            }
        }
        $shippingPrice = $this->cart->getQuote()->getShippingAddress()->getShippingAmount();
        return $finalPrice + $shippingPrice;  
    }

    /**
     * @return boolean
     */
    public function getCartProducts()
    {
        $products = $this->cart->getQuote()->getItems();
        return $products;  
    }

    /**
     * @param $price
     * @return int
     */
    public function toPLN($price)
    {
        $store = $this->storeManager->getStore();
        $currencyCode = $store->getCurrentCurrencyCode();
        $currency = $this->currencyFactory->create()->load($currencyCode);
        $avaiableCurrencies = $store->getAvailableCurrencyCodes();
        try {
            if (in_array('PLN', $avaiableCurrencies)) {
                $price = $currency->convert($price, 'PLN');
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $price;
    }

}
