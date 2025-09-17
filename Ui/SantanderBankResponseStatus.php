<?php
/**
 * @copyright Copyright (c) 2024 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Ui;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class SantanderBankResponseStatus extends Column
{
    public const SANTANDER_PAYMENT_METHOD = 'eraty_santander';

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->orderRepository = $orderRepository;
    }

    /**
     * Prepare data
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->orderRepository->get($item['entity_id']);
                $status = $order->getData('santander_bank_response_status');

                if ($order->getPayment()->getMethod() != self::SANTANDER_PAYMENT_METHOD) {
                    $item[$this->getData('name')] = __('eraty not enable for this order');
                    continue;
                }

                $item[$this->getData('name')] = $status ?: __('Processing');
            }
        }

        return $dataSource;
    }
}
