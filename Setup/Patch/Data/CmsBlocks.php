<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * CmsBlocks
 */
class CmsBlocks implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;
    /**
     * @var \Magento\Cms\Api\Data\BlockInterfaceFactory
     */
    private $blockFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     * @param \Magento\Cms\Api\Data\BlockInterfaceFactory $blockFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Cms\Api\Data\BlockInterfaceFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->createCmsBlocks();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get CMS blocks as array
     * @return array
     */
    private function getBlocks()
    {
        return [
            'eraty_success' => __('Santander application accepted'),
            'eraty_failure' => __('Santander application rejected')
        ];
    }

    /**
     * Create CMS blocks for eRaty Payment
     * @return void
     */
    private function createCmsBlocks()
    {
        foreach ($this->getBlocks() as $key => $title) {
            try {
                $this->blockRepository->getById($key);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $block = $this->blockFactory->create();
                $block->setIdentifier($key);
                $block->setTitle($title);
                $block->setIsActive(true);
                $this->blockRepository->save($block);
            }
        }
    }
    /**
     * Delete CMS blocks
     * @return void
     */
    private function deleteCmsBlocks()
    {
        foreach ($this->getBlocks() as $key => $title) {
            $this->blockRepository->deleteById($key);
        }
    }
    /**
     * Revert data patch
     * @return void
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->deleteCmsBlocks();
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
