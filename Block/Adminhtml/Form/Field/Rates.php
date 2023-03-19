<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Rates
 */
class Rates extends AbstractFieldArray
{
    /**
     * eRaty Santander rates template
     * @var string
     */
    protected $_template = 'Aurora_Santander::config/rates.phtml';

    /**
     * Prepare to render santander template
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('qty', ['label' => __('Number of installments'), 'class' => 'required-entry']);
        $this->addColumn('percent', ['label' => __('Percent'), 'class' => 'required-entry']);
        $this->addColumn('shop_number', ['label' => __('Shop number'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
