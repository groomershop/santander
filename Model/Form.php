<?php
/**
 * @copyright Copyright (c) 2025 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Model;

use Aurora\Santander\Api\Data\FormInterface;
use Aurora\Santander\Helper\ItemValidator;
use Aurora\Santander\Model\Rates;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Exception;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Form
{
    /** @var Order|null */
    private ?Order $order = null;

    /** @var int */
    private int $count = 0;

    /** @var float */
    private float $totalValue = 0;

    /** @var float */
    private float $totalQty = 0;

    /**
     * @param Session $session
     * @param ItemValidator $itemValidator
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param Rates $rates
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        protected Session $session,
        protected ItemValidator $itemValidator,
        protected StoreManagerInterface $storeManager,
        protected CurrencyFactory $currencyFactory,
        protected Rates $rates,
        protected UrlInterface $urlBuilder
    ) {
    }

    /**
     * Retrieve data for the form.
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $data = [];

        if ($order = $this->getOrder()) {
            $this->addItems($data);
            if ($order->getShippingInclTax() > 0) {
                $this->addShipping($data);
            }
            $this->addOrder($data);
            $this->addCustomer($data);
            $this->addChar($data);
            $this->addUrls($data);
            $this->addInit($data);
        }

        return $data;
    }

    /**
     * Get the last order from the session.
     *
     * @return Order|null
     */
    protected function getOrder(): ?Order
    {
        if (!$this->order) {
            $this->order = $this->session->getLastRealOrder();
        }

        return $this->order;
    }

    /**
     * Add items to the data array.
     *
     * @param array $data
     * @return void
     * @throws NoSuchEntityException
     */
    protected function addItems(array &$data): void
    {
        $order = $this->getOrder();
        foreach ($order->getItems() as $item) {
            if (!$this->itemValidator->validate($item)) {
                continue;
            }

            $qty = $item->getQtyOrdered();
            $price = $this->calculateItemPrice($item);
            $displayPrice = $this->getPricePLN($price);

            $this->count++;
            $this->totalValue += $price;
            $this->totalQty += $qty;

            $data[FormInterface::ID_TOWARU . $this->count] = $item->getProductId();
            $data[FormInterface::NAZWA_TOWARU . $this->count] = $item->getName();
            $data[FormInterface::WARTOSC_TOWARU . $this->count] = $displayPrice;
            $data[FormInterface::LICZBA_SZTUK_TOWARU . $this->count] = (int)$qty;
            $data[FormInterface::JEDNOSTKA_TOWARU . $this->count] = 'szt.';
        }
    }

    /**
     * Add shipping information to the data array.
     *
     * @param array $data
     * @return void
     * @throws NoSuchEntityException
     */
    protected function addShipping(array &$data): void
    {
        $order = $this->getOrder();

        $this->totalValue += (float)$order->getShippingInclTax();
        $this->totalQty++;
        $this->count++;

        $data[FormInterface::ID_TOWARU . $this->count] = 'KosztPrzesylki';
        $data[FormInterface::NAZWA_TOWARU . $this->count] = 'Koszt Przesyłki';
        $data[FormInterface::WARTOSC_TOWARU . $this->count] = $this->getPricePLN((float)$order->getShippingInclTax());
        $data[FormInterface::LICZBA_SZTUK_TOWARU . $this->count] = 1;
        $data[FormInterface::JEDNOSTKA_TOWARU . $this->count] = 'sztuki';
    }

    /**
     * Add order information to the data array.
     *
     * @param array $data
     * @return void
     * @throws NoSuchEntityException
     */
    protected function addOrder(array &$data): void
    {
        $order = $this->getOrder();

        $data[FormInterface::WARTOSC_TOWAROW] = $this->getPricePLN((float)$this->totalValue);
        $data[FormInterface::LICZBA_SZTUK_TOWAROW] = $this->totalQty;
        $data[FormInterface::NUMER_SKLEPU] = $this->rates->getShopId($order->getItems());
        $data[FormInterface::TYP_PRODUKTU] = 0;
        $data[FormInterface::SPOSOB_DOSTARCZENIA_TOWARU] = $order->getShippingDescription();
        $data[FormInterface::NR_ZAMOWIENIA_SKLEP] = $order->getIncrementId();
    }

    /**
     * Add customer information to the data array.
     *
     * @param array $data
     * @return void
     */
    protected function addCustomer(array &$data): void
    {
        $billingAddress = $this->getOrder()?->getBillingAddress();

        $data[FormInterface::IMIE] = $billingAddress->getFirstname();
        $data[FormInterface::NAZWISKO] = $billingAddress->getLastname();
        $data[FormInterface::EMAIL] = $billingAddress->getEmail();
        $data[FormInterface::TEL_KONTAKT] = $billingAddress->getTelephone();
        $data[FormInterface::ULICA] = implode(' ', $billingAddress->getStreet());
        $data[FormInterface::MIASTO] = $billingAddress->getCity();
        $data[FormInterface::KOD_POCZ] = $billingAddress->getPostcode();
    }

    /**
     * Add character set to the data array.
     *
     * @param array $data
     * @return void
     */
    protected function addChar(array &$data): void
    {
        $data[FormInterface::CHAR] = 'UTF';
    }

    /**
     * Add URLs to the data array.
     *
     * @param array $data
     * @return void
     */
    protected function addUrls(array &$data): void
    {
        $order = $this->getOrder();

        $data[FormInterface::WNIOSEK_ZAPISANY] = $this->getSaveOrderPageUrl([
                'order' => $order->getId(),
                'wniosek' => 'przyjety',
            ]) . 'id_zamowienia/';
        $data[FormInterface::WNIOSEK_ANULOWANY] = $this->getSaveOrderPageUrl([
                'order' => $order->getId(),
                'wniosek' => 'odrzucony',]) . 'id_zamowienia/';
    }

    /**
     * Add initialization flag to the data array.
     *
     * @param array $data
     * @return void
     */
    protected function addInit(array &$data): void
    {
        $data[FormInterface::INIT] = 1;
    }

    /**
     * Calculate price for inserted item.
     *
     * @param Item $item Order item.
     *
     * @return float Price to display.
     */
    private function calculateItemPrice(Item $item): float
    {
        $price = $item->getPriceInclTax();
        $qty = $item->getQtyOrdered();
        $discount = $item->getDiscountAmount() ?? 0;
        if ($parentItem = $item->getParentItem()) {
            $price = $parentItem->getPriceInclTax();
            $discount = $parentItem->getDiscountAmount() ?? 0;
            $qty = $parentItem->getQtyOrdered();
        }

        return ($price * $qty) - $discount;
    }

    /**
     * Retrieved price in PLN
     *
     * @param float $price
     * @return float
     * @throws NoSuchEntityException
     */
    private function getPricePLN(float $price)
    {
        $currencyCode = $this->getOrder()->getOrderCurrencyCode();
        $store = $this->storeManager->getStore();
        $currency = $this->currencyFactory->create()->load($currencyCode);
        $availableCurrencies = $store->getAvailableCurrencyCodes();
        try {
            if (in_array('PLN', $availableCurrencies)) {
                $price = $currency->convert($price, 'PLN');
            }
            // phpcs:ignore
        } catch (Exception $e) {
        }

        return $price;
    }

    /**
     * Retrieved URL for save order page
     *
     * @param array $params
     * @return string
     */
    private function getSaveOrderPageUrl(array $params)
    {
        return $this->urlBuilder->getUrl('santander/eraty/saveorder', $params);
    }
}
