<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\BinanceHelper;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class BinanceService
 * @package AppBundle\Service
 */
class BinanceService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var BinanceHelper $binanceHelper
     */
    private $binanceHelper;

    /**
     * BinanceService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->binanceHelper = new BinanceHelper(   $parameters['api_key'],
                                                    $parameters['api_secret'],
                                                    $parameters['base_uri']);
    }

    /**
     * @return TickerDTO
     */
    public function getTicker():? TickerDTO
    {
        $response = $this->getClient()->request(
            'GET',
            '/api/v3/ticker/bookTicker?symbol=BTCUSDT'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::BINANCE, $responseJson->askPrice, $responseJson->bidPrice, new \DateTime('now', new \DateTimeZone('Europe/Madrid')));

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance():? BalanceDTO
    {
        /** @var array $balance */
        $balance = $this->binanceHelper->balances();

        /**
         * @var float $usd
         */
        $usd = 0;

        /**
         * @var float $btc
         */
        $btc = 0;

        if($balance != null && count($balance) > 0 ) {
            if(array_key_exists('USDT', $balance)){
                $usd = $balance['USDT']['available'];
            }

            if(array_key_exists('BTC', $balance)){
                $btc = $balance['BTC']['available'];
            }
        }

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::BINANCE, $usd, $btc);

        return $balanceDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeBuyOrder(float $amount, float $price):? OrderDTO
    {
        /** @var array $order */
        $order = $this->binanceHelper->buy('BTCUSDT', $amount, $price);

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp->setTimestamp($order['transactTime']);
        $id         = $order['clientOrderId'];
        $price      = $order['price'];
        $amountBtc  = $order['executedQty'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::BINANCE, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_BUY);

        return $orderDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):? OrderDTO
    {
        /** @var array $order */
        $order = $this->binanceHelper->sell('BTCUSDT', $amount, $price);

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp->setTimestamp($order['transactTime']);
        $id         = $order['clientOrderId'];
        $price      = $order['price'];
        $amountBtc  = $order['executedQty'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::BINANCE, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        /** @var array $openOrders */
        $openOrders = $this->binanceHelper->openOrders('BTCUSDT');

        /** @var OrderDTO[] $result */
        $result = [];

        foreach($openOrders as $openOrder){
            $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
            $timestamp->setTimestamp($openOrder['time']);
            $orderId    = $openOrder['clientOrderId'];
            $price      = $openOrder['price'];
            $amountBtc  = $openOrder['executedQty'];
            $amountUsd  = $price * $amountBtc;
            $type       = $openOrder['side'] == 'BUY' ? OrderDTO::ORDER_TYPE_BUY : OrderDTO::ORDER_TYPE_SELL;

            /** @var OrderDTO $orderDTO */
            $orderDTO = new OrderDTO($orderId, Ticker::BINANCE, $price, $amountUsd, $amountBtc, $timestamp, $type);

            array_push($result, $orderDTO);
        }

        return $result;
    }
}
