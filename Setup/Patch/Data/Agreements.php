<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\CheckoutAgreements\Model\AgreementFactory;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository;

use Aurora\Santander\Helper\Data;

/**
 * Agreements
 */
class Agreements implements DataPatchInterface, PatchRevertableInterface
{
    const SANTANDER_AGREEMENT_ID = 'santander_configuration/agreement_configuration/santander_agreement';

    /**
     * @var AgreementFactory
     */
    protected $agreementFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CheckoutAgreementsRepository
     */
    protected $checkoutAgreementsRepository;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @param AgreementFactory $agreementFactory
     * @param ResourceConnection $resourceConnection
     * @param Data $dataHelper
     * @param CheckoutAgreementsRepository $checkoutAgreementsRepository
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        AgreementFactory $agreementFactory,
        ResourceConnection $resourceConnection,
        Data $dataHelper,
        CheckoutAgreementsRepository $checkoutAgreementsRepository,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->agreementFactory = $agreementFactory;
        $this->resourceConnection = $resourceConnection;
        $this->dataHelper = $dataHelper;
        $this->checkoutAgreementsRepository = $checkoutAgreementsRepository;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $agreementData = [
            [
                'name' => __('Santander agreement'),
                'content' => __('Zapoznałem się z procedurą udzielenia kredytu konsumenckiego na zakup towarów i usług 
                <a href="https://www.santanderconsumer.pl/raty-jak-kupic" target="_blank">eRaty Santander Consumer Bank</a>'),
                'checkbox_text' => __('Zapoznałem się z procedurą udzielenia kredytu konsumenckiego na zakup towarów i usług
                eRaty Santander Consumer Bank'),
                'is_active' => '1',
                'is_html' => '1',
                'mode' => '1',
            ],
        ];

        $agreement = $this->agreementFactory->create();
        $agreementStoreTable = $this->resourceConnection->getTableName('checkout_agreement_store');

        foreach ($agreementData as $data) {
            $agreement->setData($data)
                ->save();

            $id = $agreement->getId();
            $sql = 'INSERT INTO ' . $agreementStoreTable . ' (agreement_id, store_id) VALUES (' . $id . ', 0) ';
            $this->resourceConnection->getConnection()->query($sql);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Revert data patch
     * @return void
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $id = $this->dataHelper->getConfigValue(self::SANTANDER_AGREEMENT_ID);
        $this->checkoutAgreementsRepository->deleteById($id);
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
