<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Controller\Eraty;

use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;

class SaveOrder implements HttpGetActionInterface
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ResultFactory $resultFactory
     * @param Http $request
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ResultFactory $resultFactory,
        Http $request,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->request->getParam('order');
        $santanderId = $this->request->getParam('id_zamowienia');

        if ($santanderId === null || $id === null) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('no-route');
        }

        $order = $this->orderRepository->get($id);

        if ($order->getSantanderBankOrderNumber() !== null) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('no-route');
        }

        $order->setSantanderBankOrderNumber($santanderId);
        $this->orderRepository->save($order);

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
