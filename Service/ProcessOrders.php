<?php
/**
 * @copyright Copyright (c) 2024 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Framework\Webapi\Soap\ClientFactory as SoapClientFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Aurora\Santander\Helper\Config;

class ProcessOrders
{
    public const SANTANDER_URL = 'https://api.santanderconsumer.pl/ProposalServiceHybrid';
    public const SANTANDER_WSDL = 'https://api.santanderconsumer.pl/ProposalServiceHybrid?wsdl';
    public const SANTANDER_PAYMENT_METHOD = 'eraty_santander';
    public const SANTANDER_CLOSED_STATES = [SantanderStatus::REJECT, SantanderStatus::RELEASE];

    /** @var OrderCollectionFactory */
    private $orderCollectionFactory;

    /** @var SoapClientFactory */
    private $soapClientFactory;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Config */
    private $config;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SoapClientFactory $soapClientFactory
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        SoapClientFactory $soapClientFactory,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->soapClientFactory = $soapClientFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Retrieved order collection with Santander payment
     *
     * @return Collection
     */
    private function getOrderCollectionWithSantanderPaymentMethod()
    {
        $collection = $this->orderCollectionFactory->create();

        $collection->getSelect()->join(
            ['sop' => 'sales_order_payment'],
            'main_table.entity_id = sop.parent_id',
            ['method']
        )->where('sop.method = ?', self::SANTANDER_PAYMENT_METHOD);

        $collection->getSelect()->where(
            'not santander_bank_status_code = ?',
            StatusCodeInterface::CLOSED
        );

        return $collection;
    }

    /**
     * Retrieved application data by ID
     *
     * @param string $santanderId
     *
     * @return mixed
     */
    private function getOrderApplicationDataById($santanderId)
    {
        try {
            $client = $this->soapClientFactory->create(self::SANTANDER_WSDL, [
                'location' => self::SANTANDER_URL,
                'keep_alive' => true,
                'trace' => true,
                'local_cert' => $this->config->getCertPath(),
                'passphrase' => $this->config->getCertPasswd(),
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            if (!$client->IsActive()->IsActiveResult->IsCorrect) {
                $this->logger->error('Santander service are not active');

                return false;
            }

            $state = $client->getApplicationState([
                'Identity' => [
                    'Login' => $this->config->getLogin(),
                    'Password' => $this->config->getPassword(),
                    'ShopNumber' => $this->config->getShopNumber(),
                ],
                'ShopApplicationNumbers' => [
                    $santanderId,
                ],
            ]);

            return $state->GetApplicationStateResult->Applications->ApplicationData;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());

            return false;
        }
    }

    /**
     * Update all status
     *
     * @param OutputInterface|null $output
     *
     * @throws FileSystemException
     * @return void
     */
    public function updateStatusAll($output = null)
    {
        $orders = $this->getOrderCollectionWithSantanderPaymentMethod();

        foreach ($orders as $order) {
            $applicationData = $this->getOrderApplicationDataById($order->getIncrementId());
            $state = $applicationData?->CreditState;
            $downpayment = $applicationData?->Downpayment;

            if (!$state || !$downpayment) {
                $output->writeln(sprintf('<error>[ID: %s] something went wrong...</error>', $order->getIncrementId()));
                continue;
            }

            $order->setSantanderBankStatusCode(
                $state == array_contains(self::SANTANDER_CLOSED_STATES, $state)
                    ? StatusCodeInterface::CLOSED : StatusCodeInterface::ENABLE
            );
            $order->setSantanderBankResponseStatus($state);
            $order->setSantanderBankDownpayment((float)$downpayment);

            $this->orderRepository->save($order);

            if ($output) {
                $output->writeln(sprintf('<info>[ID: %s] Order updated correctly...</info>', $order->getIncrementId()));
            }
        }
    }
}
