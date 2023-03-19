<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Cron;

use Exception;
use Psr\Log\LoggerInterface;
use Aurora\Santander\Service\ProcessOrders;

class UpdateStatus
{
    /**
     * @var ProcessOrders
     */
    private $processOrder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProcessOrders $processOrders
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProcessOrders $processOrders,
        LoggerInterface $logger,
    ) {
        $this->processOrder = $processOrders;
        $this->logger = $logger;
    }

    /**
     * Updated all statuses
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->processOrder->updateStatusAll();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
