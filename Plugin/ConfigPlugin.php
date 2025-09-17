<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Plugin;

use Closure;
use InvalidArgumentException;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Variable\Model\VariableFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Config\Model\Config as BaseConfig;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Aurora\Santander\Model\Santander;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var AttributeOptionManagementInterface
     */
    protected $attributeOption;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepositoryInterface;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var integer
     */
    public $entityTypeId;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param AttributeOptionManagementInterface $attributeOption
     * @param AttributeOptionInterfaceFactory $optionFactory
     * @param AttributeRepositoryInterface $attributeRepositoryInterface
     * @param Config $eavConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        AttributeOptionManagementInterface $attributeOption,
        AttributeOptionInterfaceFactory $optionFactory,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        Config $eavConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->attributeOption = $attributeOption;
        $this->optionFactory = $optionFactory;
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Plugin around save method
     *
     * @param BaseConfig $subject
     * @param Closure $proceed
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @return Closure
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundSave(BaseConfig $subject, Closure $proceed)
    {
        if ($subject->getData('section') == 'payment') {
            try {
                $oldConfig = $this->serializer->unserialize(
                    $this->scopeConfig->getValue('payment/eraty_santander/ranges')
                );
            } catch (InvalidArgumentException $exception) {
                return $proceed();
            }

            $this->entityTypeId = $this->eavConfig->getEntityType(Product::ENTITY)->getEntityTypeId();
            $groups = $subject->getData('groups');
            if (array_key_exists('ranges', $groups['eraty_santander']['fields'])) {
                $newConfig = $groups['eraty_santander']['fields']['ranges']['value'];
                $attribute = $this->attributeRepositoryInterface->get(
                    Product::ENTITY,
                    Santander::ATTRIBUTE_CODE
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
     *
     * @param string $label
     *
     * @throws InputException
     * @throws StateException
     * @return void
     */
    public function addOption($label)
    {
        $option = $this->optionFactory->create();

        $option->setLabel($label);
        $option->setValue($label);

        $this->attributeOption->add(
            $this->entityTypeId,
            Santander::ATTRIBUTE_CODE,
            $option
        );
    }

    /**
     * Delete attribute dropdown option
     *
     * @param AttributeInterface $attribute
     * @param string $label
     *
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function deleteOption($attribute, $label)
    {
        $optionId = $attribute->getSource()->getOptionId($label);
        $this->attributeOption->delete(
            $this->entityTypeId,
            Santander::ATTRIBUTE_CODE,
            $optionId
        );
    }

    /**
     * Update attribute dropdown option
     *
     * @param AttributeInterface $attribute
     * @param array $options
     * @param string $oldlabel
     * @param string $newLabel
     *
     * @throws InputException
     * @throws StateException
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
     *
     * @param AttributeInterface $attribute
     *
     * @throws InputException
     * @throws StateException
     * @return array
     */
    private function getOptions($attribute)
    {
        $options = [];
        $items = $this->attributeOption->getItems(
            $this->entityTypeId,
            Santander::ATTRIBUTE_CODE
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
