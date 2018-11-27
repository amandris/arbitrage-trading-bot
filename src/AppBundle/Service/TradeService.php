<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\Entity\Difference;
use AppBundle\Entity\OrderPair;
use AppBundle\Entity\Status;
use AppBundle\Repository\OrderPairRepository;

/**
 * Class TradeService
 * @package AppBundle\Service
 */
class TradeService
{
    /** @var ExchangeServiceInterface[] $exchangeServices */
    private $exchangeServices;

    /** @var OrderPairRepository $orderPairRepository */
    private $orderPairRepository;

    /**
     * TradeService constructor.
     * @param array $exchangeServices
     * @param OrderPairRepository $orderPairRepository
     */
    public function __construct(array $exchangeServices, $orderPairRepository)
    {
        $this->exchangeServices = $exchangeServices;
        $this->orderPairRepository = $orderPairRepository;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders()
    {
        /** @var OrderDTO[] $result */
        $result = [];

        foreach($this->exchangeServices as $name => $exchangeService){
            $parameters = $exchangeService->getClient()->getParameters();
            if($parameters['enable']) {
                $orderDTOs = $exchangeService->getOrders();
                foreach ($orderDTOs as $orderDTO) {
                    array_push($result, $orderDTO);
                }
            }
        }

        return $result;
    }

    /**
     * @param Difference $difference
     * @param Status $status
     * @return OrderPair
     */
    public function placeOrderPair($difference, $status):?OrderPair
    {
        /** @var ExchangeServiceInterface $sellExchangeService */
        $sellExchangeService = $this->getExchangeServiceByName($difference->getExchangeSellName());

        /** @var ExchangeServiceInterface $buyExchangeService */
        $buyExchangeService = $this->getExchangeServiceByName($difference->getExchangeBuyName());

        if($sellExchangeService == null || $buyExchangeService == null){
            return null;
        }

        $finalAskPrice = $difference->getAsk() + $status->getAddOrSubToOrderUsd();
        $finalBidPrice = $difference->getBid() - $status->getAddOrSubToOrderUsd();

        /** @var float $amountToBuy */
        $amountToTrade = round($status->getOrderValueBtc(), 8);

        /** @var OrderDTO $buyOrderDTO */
        $buyOrderDTO = $buyExchangeService->placeBuyOrder($amountToTrade, $finalAskPrice);

        /** @var OrderPair $orderPair */
        $orderPair = null;

        if($buyOrderDTO != null) {
            $orderPair = new OrderPair();
            $orderPair->setBuyOrderId($buyOrderDTO->getOrderId());
            $orderPair->setBuyOrderAmountBtc($buyOrderDTO->getAmountBTC());
            $orderPair->setBuyOrderAmountUsd($buyOrderDTO->getAmountUSD());
            $orderPair->setBuyOrderExchange($difference->getExchangeBuyName());
            $orderPair->setBuyOrderPrice($finalAskPrice);
            $orderPair->setBuyOrderOpen(true);
            $orderPair->setBuyOrderCreated($buyOrderDTO->getCreated());

            /** @var OrderDTO $sellOrderDTO */
            $sellOrderDTO = $sellExchangeService->placeSellOrder($amountToTrade, $finalBidPrice);

            if($sellOrderDTO != null) {
                $orderPair->setSellOrderId($sellOrderDTO->getOrderId());
                $orderPair->setSellOrderAmountBtc($sellOrderDTO->getAmountBTC());
                $orderPair->setSellOrderAmountUsd($sellOrderDTO->getAmountUSD());
                $orderPair->setSellOrderExchange($difference->getExchangeSellName());
                $orderPair->setSellOrderPrice($finalBidPrice);
                $orderPair->setSellOrderOpen(true);
                $orderPair->setSellOrderCreated($sellOrderDTO->getCreated());
            }

            $this->orderPairRepository->save($orderPair);
        }

        return $orderPair;
    }

    /**
     * @param $name
     * @return ExchangeServiceInterface
     */
    private function getExchangeServiceByName($name):ExchangeServiceInterface
    {
        foreach ($this->exchangeServices as $exchangeName=>$exchangeService){
            if($exchangeName === $name){
                return $exchangeService;
            }
        }

        return null;
    }
}
