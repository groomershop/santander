<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Block\Form;

/**
 * Block for eRaty Santander payment method form
 */
class Santander extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * eRaty Santander template
     * @var string
     */
    protected $_template = 'Aurora_Santander::form/santander.phtml';
}
