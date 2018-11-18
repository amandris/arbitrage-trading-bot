<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\BittrexHelper;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class BittrexService
 * @package AppBundle\Service
 */
class BittrexService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var BittrexHelper $bittrexHelper
     */
    private $bittrexHelper;

    /**
     * BittrexService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->bittrexHelper = new BittrexHelper(   $parameters['api_key'],
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
            '/public/getticker?market=USD-BTC'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        $timestamp = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::BITTREX, $responseJson->result->Ask, $responseJson->result->Bid, $timestamp);

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance():? BalanceDTO
    {

        /** @var array $balances */
        $balances = $this->bittrexHelper->getBalances();

        /**
         * @var float $usd
         */
        $usd = 0;

        /**
         * @var float $btc
         */
        $btc = 0;

        if($balances != null && count($balances) > 0 ) {
            foreach ($balances as $balance) {
                if ($balance->Currency === 'BTC') {
                    $btc = $balance->Available;
                }

                if ($balance->Currency === 'USD') {
                    $usd = $balance->Available;
                }
            }
        }

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::BITTREX, $usd, $btc);

        return $balanceDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeBuyOrder(float $amount, float $price):? OrderDTO
    {
        try {
            /** @var array $order */
            $order = $this->bittrexHelper->buyLimit('USD-BTC', $amount, $price);
        }catch(\Exception $e){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $id         = $order['uuid'];
        $amountUsd  = $price * $amount;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::BITTREX, $price, $amountUsd, $amount,$timestamp,OrderDTO::ORDER_TYPE_BUY);

        return $orderDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):? OrderDTO
    {
        try{
            /** @var array $order */
            $order = $this->bittrexHelper->sellLimit('USD-BTC', $amount, $price);
        }catch(\Exception $e){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $id         = $order['uuid'];
        $amountUsd  = $price * $amount;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::BITTREX, $price, $amountUsd, $amount,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        try {
            /** @var array $openOrders */
            $openOrders = $this->bittrexHelper->getOpenOrders('USD-BTC');
        }catch(\Exception $e){
            return null;
        }

        /** @var OrderDTO[] $result */
        $result = [];

        foreach($openOrders as $openOrder){
            if(!$openOrder['closed']) {
                $timestamp = new \DateTime($openOrder['datetime'], new \DateTimeZone('Europe/Madrid'));
                $orderId = $openOrder['OrderUuid'];
                $price = $openOrder['price'];
                $amountBtc = $openOrder['amount'];
                $amountUsd = $price * $amountBtc;
                $type = $openOrder['OrderType'] == 'LIMIT_BUY' ? OrderDTO::ORDER_TYPE_BUY : OrderDTO::ORDER_TYPE_SELL;

                /** @var OrderDTO $orderDTO */
                $orderDTO = new OrderDTO($orderId, Ticker::BITTREX, $price, $amountUsd, $amountBtc, $timestamp, $type);

                array_push($result, $orderDTO);
            }
        }

        return $result;
    }
}
