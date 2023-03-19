<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Model;

use Magento\Payment\Block\Info\Instructions;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Santander\Block\Form\Santander as MagentoSantander;

class Santander extends AbstractMethod
{
    public const ATTRIBUTE_CODE = 'santander_installment';

    public const PAYMENT_METHOD_CODE = 'eraty_santander';

    public const MIN_ORDER_TOTAL = 'min_order_total';

    public const MAX_ORDER_TOTAL = 'max_order_total';

    /**
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = MagentoSantander::class;

    /**
     * @var string
     */
    protected $_infoBlockType = Instructions::class;

    /**
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
