<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\ViewModel;

use Exception;
use Psr\Log\LoggerInterface;

use Magento\Framework\Registry;
use Magento\Checkout\Helper\Cart;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

use Aurora\Santander\Model\Santander;
use Aurora\Santander\Helper\Data as AuroraData;

/**
 * Installment
 */
class Installment implements ArgumentInterface
{
    const MIN_PRODUCT_PRICE = 'santander_configuration/calc_button_configuration/min_product_price';
    const MAX_PRODUCT_PRICE = 'santander_configuration/calc_button_configuration/max_product_price';
    const MIN_ORDER_TOTAL = 'payment/eraty_santander/min_order_total';
    const MAX_ORDER_TOTAL = 'payment/eraty_santander/max_order_total';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $priceHelper;

    /**
     * @var AuroraData
     */
    protected $dataHelper;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var StoreManagerInterface
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
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * @param Registry $registry
     * @param Data $priceHelper
     * @param AuroraData $dataHelper ,
     * @param Cart $cart ,
     * @param ProductRepositoryInterface $productRepository ,
     * @param CurrencyFactory $currencyFactory ,
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Registry $registry,
        Data $priceHelper,
        AuroraData $dataHelper,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        CurrencyFactory $currencyFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
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
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * Calculate installment qty and value.
     *
     * @param Product $product
     *
     * @return void
     */
    public function calculateInstallment($product)
    {
        $this->qty = null;
        $this->percent = null;
        $this->price = $product->getFinalPrice();
        $attribute = $product->getResource()->getAttribute(Santander::ATTRIBUTE_CODE);

        if (!$attribute) {
            return;
        }

        $label = $attribute->getFrontend()->getValue($product);

        if ($label) {
            preg_match('/(\d+)\s*x\s*(.*)\s*\%/', $label, $matches, PREG_OFFSET_CAPTURE);
        }

        if (isset($matches[1][0]) && isset($matches[2][0])) {
            $this->qty = (int)$matches[1][0];
            $this->percent = (float)$matches[2][0];
        }
    }

    /**
     * Get unit installment price
     *
     * @return float
     */
    public function getPrice()
    {
        if ($this->percent > 0) {
            return $this->priceHelper->currency(($this->price * (1 + ($this->percent / 100))) / $this->qty, true,
                false);
        }

        return $this->priceHelper->currency($this->price / $this->qty, true, false);
    }

    /**
     * @param $price
     *
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
     *
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
     *
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
     *
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
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $price;
    }
}
