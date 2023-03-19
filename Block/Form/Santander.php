<?php

/**
 * @copyright Copyright (c) 2023 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

namespace Aurora\Santander\Block\Form;

use Magento\OfflinePayments\Block\Form\AbstractInstruction;
use Magento\Sales\Model\Order\Item;

/**
 * Block for eRaty Santander payment method form.
 */
class Santander extends AbstractInstruction
{
    /**
     * eRaty Santander template
     *
     * @var string
     */
    protected $_template = 'Aurora_Santander::form/santander.phtml';

    /**
     * Calculate price for inserted item.
     *
     * @param Item $item Order item.
     *
     * @return float Price to display.
     */
    public function calculateItemPrice(Item $item): float
    {
        $price = $item->getPriceInclTax();
        $qty = $item->getQtyOrdered();
        $discount = $item->getDiscountAmount() ?? 0;

        return ($price * $qty) - $discount;
    }
}
