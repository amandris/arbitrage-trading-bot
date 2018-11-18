<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\KrakenHelper;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class KrakenService
 * @package AppBundle\Service
 */
class KrakenService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var KrakenHelper $krakenHelper
     */
    private $krakenHelper;

    /**
     * KrakenService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->krakenHelper = new KrakenHelper( $parameters['api_key'],
            $parameters['api_secret'],
            $parameters['base_uri']);
    }

    /**
     * @return TickerDTO
     */
    public function getTicker():? TickerDTO
    {
        try {
            $response = $this->getClient()->request(
                'GET',
                '/0/public/Ticker?pair=xbtusd'
            );
        }catch (\Exception $e){
            return null;
        }

        $responseJson = json_decode($response->getBody()->getContents());

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::KRAKEN, $responseJson->result->XXBTZUSD->a[0], $responseJson->result->XXBTZUSD->b[0], new \DateTime('now', new \DateTimeZone('Europe/Madrid')));

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance():? BalanceDTO
    {
        try {
            /** @var array $balance */
            $balance = $this->krakenHelper->queryPrivate('Balance');
        } catch (\Exception $e){
            return null;
        }

        /** @var float $usd */
        $usd = $balance['result']['ZUSD'];

        /** @var float $btc */
        $btc = $balance['result']['XXBT'];

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::KRAKEN, $usd, $btc);

        return $balanceDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeBuyOrder(float $amount, float $price):? OrderDTO
    {
        $query = ['pair' => 'xbtusd', 'type' => 'buy', 'ordertype' => 'limit', 'price' => $price, 'volumen' => $amount];

        try {
            /** @var array $order */
            $order = $this->krakenHelper->queryPrivate('AddOrder', $query);
        } catch (\Exception $e){
            return null;
        }

        if(array_key_exists('error', $order) && count($order['error']) > 0){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));

        $id         = $order['refid'];
        $price      = $order['price'];
        $amountBtc  = $order['vol'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::KRAKEN, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_BUY);

        return $orderDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):? OrderDTO
    {
        $query = ['pair' => 'xbtusd', 'type' => 'sell', 'ordertype' => 'limit', 'price' => $price, 'volumen' => $amount];

        try {
            /** @var array $order */
            $order = $this->krakenHelper->queryPrivate('AddOrder', $query);
        } catch (\Exception $e){
            return null;
        }

        if(array_key_exists('error', $order) && count($order['error']) > 0){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $id         = $order['refid'];
        $price      = $order['price'];
        $amountBtc  = $order['vol'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::KRAKEN, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        try {
            /** @var array $openOrders */
            $openOrders = $this->krakenHelper->queryPrivate('OpenOrders');
        } catch (\Exception $e){
            return [];
        }

        /** @var OrderDTO[] $result */
        $result = [];

        foreach($openOrders['result']['open'] as $openOrder){
            if($openOrder['status'] == 'open') {
                $timestamp = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
                $timestamp->setTimestamp($openOrder['opentm']);
                $orderId = $openOrder['refid'];
                $price = $openOrder['price'];
                $amountBtc = $openOrder['vol'];
                $amountUsd = $price * $amountBtc;
                $type = $openOrder['descr']['type'] == 'buy' ? OrderDTO::ORDER_TYPE_BUY : OrderDTO::ORDER_TYPE_SELL;

                /** @var OrderDTO $orderDTO */
                $orderDTO = new OrderDTO($orderId, Ticker::KRAKEN, $price, $amountUsd, $amountBtc, $timestamp, $type);

                array_push($result, $orderDTO);
            }
        }

        return $result;
    }
}
