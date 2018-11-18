<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\ItbitHelper;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class ItbitService
 * @package AppBundle\Service
 */
class ItbitService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var ItbitHelper $itbitHelper
     */
    private $itbitHelper;

    /**
     * ItbitService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->itbitHelper = new ItbitHelper(   $parameters['api_secret'],
                                                $parameters['api_key'],
                                                $parameters['user_id'],
                                                $parameters['base_uri'].'/');
    }

    /**
     * @return TickerDTO
     */
    public function getTicker():? TickerDTO
    {
        $response = $this->getClient()->request(
            'GET',
            '/markets/XBTUSD/ticker/'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::ITBIT, $responseJson->ask, $responseJson->bid, new \DateTime($responseJson->serverTimeUTC, new \DateTimeZone('Europe/Madrid')));

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance():? BalanceDTO
    {
        $responseJson = $this->itbitHelper->wallet()[0];

        /** @var float $usd */
        $usd = 0;

        /** @var float $btc */
        $btc = 0;

        foreach($responseJson->balances as $balance){
            if($balance->currency === 'USD'){
                $usd = $balance->availableBalance;
            }
            if($balance->currency === 'XBT'){
                $btc = $balance->availableBalance;
            }
            break;
        }

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::ITBIT, $usd, $btc);

        return $balanceDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeBuyOrder(float $amount, float $price):? OrderDTO
    {
        $wallet = $this->itbitHelper->wallet()[0];

        /** @var array $order */
        $order = $this->itbitHelper->create_order($wallet->id, 'buy', $amount, $price);

        if(array_key_exists('code', $order)){
            return null;
        }

        $timestamp  = new \DateTime($order['createdTime'], new \DateTimeZone('Europe/Madrid'));
        $id         = $order['id'];
        $price      = $order['price'];
        $amountBtc  = $order['amount'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::ITBIT, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_BUY);

        return $orderDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):? OrderDTO
    {
        $wallet = $this->itbitHelper->wallet()[0];

        /** @var array $order */
        $order = $this->itbitHelper->create_order($wallet->id, 'sell', $amount, $price);

        if(array_key_exists('code', $order)){
            return null;
        }

        $timestamp  = new \DateTime($order['createdTime'], new \DateTimeZone('Europe/Madrid'));
        $id         = $order['id'];
        $price      = $order['price'];
        $amountBtc  = $order['amount'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::ITBIT, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        $wallet = $this->itbitHelper->wallet()[0];

        /** @var array $openOrders */
        $openOrders = $this->itbitHelper->orders($wallet->id);

        /** @var OrderDTO[] $result */
        $result = [];

        foreach($openOrders as $openOrder){
            if($openOrder->status == 'open') {
                $timestamp = new \DateTime($openOrder->createdTime, new \DateTimeZone('Europe/Madrid'));
                $orderId = $openOrder->id;
                $price = $openOrder->price;
                $amountBtc = $openOrder->amount;
                $amountUsd = $price * $amountBtc;
                $type = $openOrder->side == 'buy' ? OrderDTO::ORDER_TYPE_BUY : OrderDTO::ORDER_TYPE_SELL;

                /** @var OrderDTO $orderDTO */
                $orderDTO = new OrderDTO($orderId, Ticker::ITBIT, $price, $amountUsd, $amountBtc, $timestamp, $type);

                array_push($result, $orderDTO);
            }
        }

        return $result;
    }
}
