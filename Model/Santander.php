<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Model;

/**
 * Class Santander
 */
class Santander extends \Magento\Payment\Model\Method\AbstractMethod
{
    const ATTRIBUTE_CODE = 'santander_installment';
    const PAYMENT_METHOD_CODE = 'eraty_santander';
    const MIN_ORDER_TOTAL = 'min_order_total';
    const MAX_ORDER_TOTAL = 'max_order_total';

    /**
     * Payment code
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * eRaty Santander payment block paths
     * @var string
     */
    protected $_formBlockType = \Magento\Santander\Block\Form\Santander::class;

    /**
     * Instructions block path
     * @var string
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;
    /**
     * Availability option
     * @var bool
     */
    protected $_isOffline = true;
    /**
     * Get instructions text from config
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
