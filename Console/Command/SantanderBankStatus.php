<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Console\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Aurora\Santander\Service\ProcessOrders;

class SantanderBankStatus extends Command
{
    /**
     * @var ProcessOrders
     */
    private $processOrders;

    /**
     * @param ProcessOrders $processOrders
     */
    public function __construct(ProcessOrders $processOrders)
    {
        parent::__construct();

        $this->processOrders = $processOrders;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('santander:bank:status:update');
        $this->setDescription('Update santander orders status');
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->processOrders->updateStatusAll($output);
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }
}
