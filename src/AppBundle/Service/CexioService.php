<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\CexioHelper;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class CexioService
 * @package AppBundle\Service
 */
class CexioService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var CexioHelper $cexioHelper
     */
    private $cexioHelper;

    /**
     * CexioService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->cexioHelper = new CexioHelper($parameters['user_id'],
                                                $parameters['api_key'],
                                                $parameters['api_secret'],
                                        $parameters['base_uri'].'/');
    }

    /**
     * @return TickerDTO
     */
    public function getTicker():? TickerDTO
    {
        $response = $this->getClient()->request(
            'GET',
            '/ticker/BTC/USD'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        $timestamp = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp->setTimestamp($responseJson->timestamp);

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::CEXIO, $responseJson->ask, $responseJson->bid, $timestamp);

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance():? BalanceDTO
    {

        /** @var array $balance */
        $balance = $this->cexioHelper->balance();

        /**
         * @var float $usd
         */
        $usd = $balance['USD']['available'];

        /**
         * @var float $btc
         */
        $btc = $balance['BTC']['available'];

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::CEXIO, $usd, $btc);

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
        $order = $this->cexioHelper->place_order('buy',$amount, $price, 'BTC/USD');

        if(array_key_exists('error', $order)){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp->setTimestamp($order['time']);
        $id         = $order['id'];
        $price      = $order['price'];
        $amountBtc  = $order['amount'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::CEXIO, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_BUY);

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
        $order = $this->cexioHelper->place_order('sell',$amount, $price, 'BTC/USD');

        if(array_key_exists('error', $order)){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp->setTimestamp($order['time']);
        $id         = $order['id'];
        $price      = $order['price'];
        $amountBtc  = $order['amount'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::CEXIO, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        /** @var array $openOrders */
        $openOrders = $this->cexioHelper->open_orders('BTC/USD');

        /** @var OrderDTO[] $result */
        $result = [];

        foreach($openOrders as $openOrder){
            if(!property_exists($openOrder, 'time')){
                continue;
            }
            $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
            $timestamp->setTimestamp($openOrder['time']);
            $orderId    = $openOrder['id'];
            $price      = $openOrder['price'];
            $amountBtc  = $openOrder['amount'];
            $amountUsd  = $price * $amountBtc;
            $type       = $openOrder['type'] == 'buy' ? OrderDTO::ORDER_TYPE_BUY : OrderDTO::ORDER_TYPE_SELL;

            /** @var OrderDTO $orderDTO */
            $orderDTO = new OrderDTO($orderId, Ticker::CEXIO, $price, $amountUsd, $amountBtc, $timestamp, $type);

            array_push($result, $orderDTO);
        }

        return $result;
    }
}
