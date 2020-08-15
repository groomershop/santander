<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Plugin;

/**
 * Class Installment
 */
class Installment
{
    /**
     * @var \Aurora\Santander\ViewModel\InstallmentFactory
     */
    public $installmentFactory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    public $json;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    public $eavAttribute;

    /**
     * @param \Aurora\Santander\ViewModel\InstallmentFactory $installmentFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     */
    public function __construct(
        \Aurora\Santander\ViewModel\InstallmentFactory $installmentFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        $this->installmentFactory = $installmentFactory;
        $this->json = $json;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $installments = [];

        $configurable = $subject->getProduct();
        $products = $configurable->getTypeInstance()->getUsedProducts(
            $configurable,
            [
                $this->eavAttribute->getIdByCode(
                    \Magento\Catalog\Model\Product::ENTITY,
                    \Aurora\Santander\Model\Santander::ATTRIBUTE_CODE
                )
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
