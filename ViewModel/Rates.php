<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\ViewModel;

/**
 * Rates
 */
class Rates implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    /**
     * Get installment rates from config
     * @return array|null
     */
    private function getRatesFromConfig()
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

    /**
     * Get Santander shopId based on installments
     * @param \Magento\Sales\Model\Order\Item[] $items
     * @return integer
     */
    public function getShopId($items)
    {
        $labels = [];
        $rates = $this->getRatesFromConfig();

        foreach ($items as $item) {
            $product = $item->getProduct();
            $label = $product->getResource()->getAttribute(\Aurora\Santander\Model\Santander::ATTRIBUTE_CODE)
                ->getFrontend()
                ->getValue($product);
            if ($label) {
                $labels[] = $label;
            }
        }

        foreach ($rates as $rate) {
            $rateLabel = $rate['qty'] . ' x ' . $rate['percent'] . '%';

            foreach ($labels as $label) {
                if ($rateLabel == $label) {
                    return $rate['shop_number'];
                }
            }
        }

        return 0;
    }
}
