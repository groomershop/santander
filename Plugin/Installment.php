<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Aurora\Santander\Model\Santander;
use Aurora\Santander\ViewModel\InstallmentFactory;

class Installment
{
    /**
     * @var InstallmentFactory
     */
    public $installmentFactory;

    /**
     * @var Json
     */
    public $json;

    /**
     * @var Json
     */
    public $eavAttribute;

    /**
     * @param InstallmentFactory $installmentFactory
     * @param Json $json
     * @param Attribute $eavAttribute
     */
    public function __construct(
        InstallmentFactory $installmentFactory,
        Json $json,
        Attribute $eavAttribute
    ) {
        $this->installmentFactory = $installmentFactory;
        $this->json = $json;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * Plugin after getJsonConfig method
     *
     * @param Configurable $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetJsonConfig(
        Configurable $subject,
        $result
    ) {
        $installments = [];

        $configurable = $subject->getProduct();
        $products = $configurable->getTypeInstance()->getUsedProducts(
            $configurable,
            [
                $this->eavAttribute->getIdByCode(
                    Product::ENTITY,
                    Santander::ATTRIBUTE_CODE
                ),
            ]
        );

        foreach ($products as $product) {
            $installment = $this->installmentFactory->create();
            $installment->calculateInstallment($product);

            $value = '';
            if ($installment->qty !== null && $installment->percent !== null) {
                $price = $installment->getPrice();
                $value = $installment->qty . ' ' . __('installments') . ' ' .
                    $installment->percent . '%' . __('on') . $installment->getPrice();
            }
            $installments[$product->getId()] = $value;
        }

        $config = $this->json->unserialize($result);
        $config = array_merge($config, ['installments' => $installments]);

        return $this->json->serialize($config);
    }
}
