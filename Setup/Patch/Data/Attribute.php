<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Attribute
 */
class Attribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Cms\Api\Data\BlockInterfaceFactory $blockFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            \Aurora\Santander\Model\Santander::ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'label' => __('Installments Santander'),
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 100,
                'position' => 100,
                'group' => 'Product Details',
                'is_filterable_in_grid' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'required' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Revert data patch
     * @return void
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            \Aurora\Santander\Model\Santander::ATTRIBUTE_CODE
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
