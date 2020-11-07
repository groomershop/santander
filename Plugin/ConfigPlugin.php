<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Plugin;

use Magento\Variable\Model\VariableFactory;

/**
 * Class ConfigPlugin
 */
class ConfigPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $attributeOption;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepositoryInterface;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var integer
     */
    public $entityTypeId;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOption
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOption,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->attributeOption = $attributeOption;
        $this->optionFactory = $optionFactory;
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     * @param \Closure $proceed
     * @return \Closure
     */
    public function aroundSave(
        \Magento\Config\Model\Config $subject,
        \Closure $proceed
    ) {
        if ($subject->getData('section') == 'payment') {
            $rates = [];

            try {
                $oldConfig = $this->serializer->unserialize(
                    $this->scopeConfig->getValue('payment/eraty_santander/ranges')
                );
            } catch (\InvalidArgumentException $exception) {
                return $proceed();
            }

            $this->entityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntityTypeId();
            $groups = $subject->getData('groups');
            if (array_key_exists('ranges', $groups['eraty_santander']['fields'])) {
                $newConfig = $groups['eraty_santander']['fields']['ranges']['value'];
                $attribute = $this->attributeRepositoryInterface->get(
                    \Magento\Catalog\Model\Product::ENTITY,
                    \Aurora\Santander\Model\Santander::ATTRIBUTE_CODE
                );
    
                foreach ($newConfig as $key => $value) {
                    if (isset($value['qty']) && isset($value['percent'])) {
                        $newLabel = $value['qty'] . ' x ' . $value['percent'] . '%';
                        if (array_key_exists($key, $oldConfig)) {
                            $oldLabel = $oldConfig[$key]['qty'] . ' x ' . $oldConfig[$key]['percent'] . '%';
                            $options = $this->getOptions($attribute);
                            $this->updateOption($attribute, $options, $oldLabel, $newLabel);
                        } else {
                            $this->addOption($newLabel);
                        }
                    }
                }
    
                foreach ($oldConfig as $key => $value) {
                    if (!array_key_exists($key, $newConfig)) {
                        $label = $value['qty'] . ' x ' . $value['percent'] . '%';
                        $this->deleteOption($attribute, $label);
                    }
                }
            }
        }
            
        return $proceed();
    }

    /**
     * Add attribute dropdown option
     * @param string $label
     * @return void
     */
    public function addOption($label)
    {
        $option = $this->optionFactory->create();
        $option->setLabel($label);
        $option->setValue($label);
        $this->attributeOption->add(
            $this->entityTypeId,
            \Aurora\Santander\Model\Santander::ATTRIBUTE_CODE,
            $option
        );
    }
    /**
     * Delete attribute dropdown option
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param string $label
     * @return void
     */
    public function deleteOption($attribute, $label)
    {
        $optionId = $attribute->getSource()->getOptionId($label);
        $options = $this->attributeOption->delete(
            $this->entityTypeId,
            \Aurora\Santander\Model\Santander::ATTRIBUTE_CODE,
            $optionId
        );
    }
    /**
     * Update attribute dropdown option
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param array$options
     * @param string $oldlabel
     * @param string $newLabel
     * @return void
     */
    public function updateOption($attribute, $options, $oldlabel, $newLabel)
    {
        $eddited = false;
        foreach ($options as $key => $option) {
            if ($option[0] == $oldlabel) {
                $options[$key] = [0 => $newLabel];
                $eddited = true;
                break;
            }
        }

        if ($eddited) {
            $data['option']['value'] = $options;
            $attribute->addData($data);
            $attribute->save();
        } else {
            $this->addOption($newLabel);
        }
    }
    /**
     * Get attribute dropdown options as array
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return void
     */
    private function getOptions($attribute)
    {
        $options = [];
        $items = $this->attributeOption->getItems(
            $this->entityTypeId,
            \Aurora\Santander\Model\Santander::ATTRIBUTE_CODE
        );

        foreach ($items as $item) {
            $id = $attribute->getSource()->getOptionId($item->getlabel());
            if ((int)$id > 0) {
                $options[$id] = [0 => $item->getlabel()];
            }
        }
        return $options;
    }
}
