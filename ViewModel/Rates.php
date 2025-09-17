<?php
/**
 * @copyright Copyright (c) 2024 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\ViewModel;

use Aurora\Santander\Model\Rates as RatesModel;
use Magento\Sales\Model\Order\Item;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Rates implements ArgumentInterface
{
    /**
     * @param RatesModel $ratesModel
     */
    public function __construct(
        protected RatesModel $ratesModel
    ) {
    }

    /**
     * Get Santander shopId based on installments
     *
     * @param Item[] $items
     * @return int
     * @throws NoSuchEntityException
     */
    public function getShopId($items)
    {
        return $this->ratesModel->getShopId($items);
    }
}
