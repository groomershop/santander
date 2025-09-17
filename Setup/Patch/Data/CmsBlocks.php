<?php
/**
 * @copyright Copyright (c) 2024 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class CmsBlocks implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var BlockInterfaceFactory
     */
    private $blockFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockInterfaceFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockRepositoryInterface $blockRepository,
        BlockInterfaceFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->createCmsBlocks();
        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }

    /**
     * Get CMS blocks as array
     *
     * @return array
     */
    private function getBlocks()
    {
        return [
            'eraty_success' => __('Santander application accepted'),
            'eraty_failure' => __('Santander application rejected'),
        ];
    }

    /**
     * Create CMS blocks for eRaty Payment
     *
     * @return void
     */
    private function createCmsBlocks()
    {
        foreach ($this->getBlocks() as $key => $title) {
            try {
                $this->blockRepository->getById($key);
            } catch (NoSuchEntityException $exception) {
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
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function deleteCmsBlocks()
    {
        foreach ($this->getBlocks() as $key => $title) {
            $this->blockRepository->deleteById($key);
        }
    }

    /**
     * Revert data patch
     *
     * @return void
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->deleteCmsBlocks();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Retrieved dependiences
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Retrieved aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
