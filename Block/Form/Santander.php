<?php

/**
 * @copyright Copyright (c) 2023 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Block\Form;

use Aurora\Santander\Helper\ItemValidator;
use Magento\Framework\View\Element\Template\Context;
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
     * @var ItemValidator
     */
    private $itemValidator;

    /**
     * Santander block constructor.
     *
     * @param ItemValidator $itemValidator
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ItemValidator $itemValidator,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->itemValidator = $itemValidator;
    }

    /**
     * Validate given `Item` instance.
     *
     * @param Item $item `Item` instance to validate.
     *
     * @return bool [ Valid / Invalid ]
     */
    public function validateItem(Item $item): bool
    {
        return $this->itemValidator->validate($item);
    }

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
        if ($parentItem = $item->getParentItem()) {
            $price = $parentItem->getPriceInclTax();
            $discount = $parentItem->getDiscountAmount() ?? 0;
            $qty = $parentItem->getQtyOrdered();
        }

        return ($price * $qty) - $discount;
    }
}
