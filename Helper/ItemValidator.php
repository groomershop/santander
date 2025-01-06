<?php

/**
 * @copyright Copyright (c) 2023 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Helper;

use Exception;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Model\Order\Item;

/**
 * Validate product to check if it should be included in final form.
 */
class ItemValidator
{
    /**
     * @var array Registered rules to validate `Item` instances.
     */
    private $rules;

    /**
     * ProductValidator constructor with default declared rules.
     *
     * @param bool $default Should use default rules flag.
     */
    public function __construct(bool $default = true)
    {
        $default ? $this->rules = [
            'validate-not-configurable' => function (Item $item) {
                return $item->getProductType() !== Configurable::TYPE_CODE;
            },
        ] : $this->rules = [];
    }

    /**
     * Add new rule to the list of rules.
     *
     * @param string $name Name of the rule.
     * @param callable $rule Callable anonymous function.
     *
     * @throws Exception Throw if rule is not callable.
     */
    public function addRule(string $name, callable $rule)
    {
        if (!is_callable($rule)) {
            throw new Exception('rule must be callable');
        }

        $this->rules[$name] = $rule;
    }

    /**
     * Validate `Item` instance.
     *
     * @param Item $item `Item` instance to validate.
     *
     * @return bool [ Valid / Invalid ]
     */
    public function validate(Item $item): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule($item)) {
                return false;
            }
        }

        return true;
    }
}
