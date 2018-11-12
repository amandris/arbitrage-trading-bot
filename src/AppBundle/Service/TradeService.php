<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\Entity\Difference;
use AppBundle\Entity\OrderPair;
use AppBundle\Entity\Status;
use AppBundle\Entity\Ticker;
use AppBundle\Repository\BalanceRepository;
use AppBundle\Repository\OrderPairRepository;
use AppBundle\Repository\StatusRepository;

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
     * @param Difference $difference
     * @param Status $status
     * @return OrderPair
     */
    public function placeOrderPair($difference, $status):OrderPair
    {
        /** @var ExchangeServiceInterface $buyExchangeService */
        $buyExchangeService = $this->getExchangeServiceByName($difference->getExchangeAskName());

        /** @var ExchangeServiceInterface $sellExchangeService */
        $sellExchangeService = $this->getExchangeServiceByName($difference->getExchangeBidName());

        if($buyExchangeService == null || $sellExchangeService == null){
            return null;
        }

        $finalAskPrice = $difference->getAsk() + $status->getAddOrSubToOrderUsd();
        $finalBidPrice = $difference->getBid() - $status->getAddOrSubToOrderUsd();

        /** @var OrderDTO $buyOrderDTO */
        $buyOrderDTO = $buyExchangeService->placeBuyOrder($status->getOrderValueUsd() / $finalAskPrice, $finalAskPrice);

        /** @var OrderPair $orderPair */
        $orderPair = null;

        if($buyOrderDTO != null) {
            $orderPair = new OrderPair();
            $orderPair->setBuyOrderId($buyOrderDTO->getOrderId());
            $orderPair->setBuyOrderAmountBtc($buyOrderDTO->getAmountBTC());
            $orderPair->setBuyOrderAmountUsd($buyOrderDTO->getAmountUSD());
            $orderPair->setBuyOrderExchange($difference->getExchangeAskName());
            $orderPair->setBuyOrderPrice($finalAskPrice);
            $orderPair->setBuyOrderOpen(true);
            $orderPair->setBuyOrderCreated($buyOrderDTO->getCreated());

            /** @var OrderDTO $sellOrderDTO */
            $sellOrderDTO = $sellExchangeService->placeSellOrder($status->getOrderValueUsd() / $finalBidPrice, $finalBidPrice);

            $orderPair->setSellOrderId($sellOrderDTO->getOrderId());
            $orderPair->setSellOrderAmountBtc($sellOrderDTO->getAmountBTC());
            $orderPair->setSellOrderAmountUsd($sellOrderDTO->getAmountUSD());
            $orderPair->setSellOrderExchange($difference->getExchangeBidName());
            $orderPair->setSellOrderPrice($finalBidPrice);
            $orderPair->setSellOrderOpen(true);
            $orderPair->setSellOrderCreated($sellOrderDTO->getCreated());

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
