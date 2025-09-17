<?php
/**
 * @copyright Copyright (c) 2025 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Model;

use InvalidArgumentException;

use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Aurora\Santander\Model\Santander;

class Rates
{
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
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    /**
     * Get installment rates from config
     *
     * @throws NoSuchEntityException
     * @return array|null
     */
    private function getRatesFromConfig()
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
     * Get Santander shopId based on installments
     *
     * @param Item[] $items
     * @throws NoSuchEntityException
     * @return integer
     */
    public function getShopId($items)
    {
        $labels = [];
        $rates = $this->getRatesFromConfig();

        if (!$rates) {
            return 0;
        }

        foreach ($items as $item) {
            $product = $item->getProduct();
            $label = $product->getResource()->getAttribute(Santander::ATTRIBUTE_CODE)
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
