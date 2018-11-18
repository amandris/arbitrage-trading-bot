<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\QuadrigacxHelper;
use AppBundle\Service\Client\ExternalClientInterface;
use stdClass;

/**
 * Class QuadrigacxService
 * @package AppBundle\Service
 */
class QuadrigacxService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var QuadrigacxHelper $quadrigacxHelper
     */
    private $quadrigacxHelper;

    /**
     * QuadrigacxService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->quadrigacxHelper = new QuadrigacxHelper( $parameters['api_key'],
                                                        $parameters['api_secret'],
                                                        $parameters['client_id'],
                                                $parameters['base_uri'].'/');
    }

    /**
     * @return TickerDTO
     */
    public function getTicker():? TickerDTO
    {
        $response = $this->getClient()->request(
            'GET',
            '/ticker?book=btc_usd'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        $timestamp = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp->setTimestamp($responseJson->timestamp);

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::QUADRIGACX, $responseJson->ask, $responseJson->bid, $timestamp);

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance():? BalanceDTO
    {
        /** @var Stdclass $balance */
        $balance = $this->quadrigacxHelper->balance();

        /** @var float $usd */
        $usd = $balance->usd_available;

        /** @var float $btc */
        $btc = $balance->btc_available;

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::QUADRIGACX, $usd, $btc);

        return $balanceDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeBuyOrder(float $amount, float $price):? OrderDTO
    {
        /** @var Stdclass $order */
        $order = $this->quadrigacxHelper->buy('btc_usd', $amount, $price);

        if($order && property_exists($order, 'error')){
            return null;
        }

        $timestamp  = new \DateTime($order->datetime, new \DateTimeZone('Europe/Madrid'));
        $id         = $order->id;
        $price      = $order->price;
        $amountBtc  = $order->amount;
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::QUADRIGACX, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_BUY);

        return $orderDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):? OrderDTO
    {
        /** @var Stdclass $order */
        $order = $this->quadrigacxHelper->sell('btc_usd', $amount, $price);

        if($order && property_exists($order, 'error')){
            return null;
        }

        $timestamp  = new \DateTime($order->datetime, new \DateTimeZone('Europe/Madrid'));
        $id         = $order->id;
        $price      = $order->price;
        $amountBtc  = $order->amount;
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::QUADRIGACX, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        /** @var array $openOrders */
        $openOrders = $this->quadrigacxHelper->open_orders('btc_usd');

        /** @var OrderDTO[] $result */
        $result = [];

        foreach($openOrders as $openOrder){
            $timestamp  = new \DateTime($openOrder->datetime, new \DateTimeZone('Europe/Madrid'));
            $orderId    = $openOrder->id;
            $price      = $openOrder->price;
            $amountBtc  = $openOrder->amount;
            $amountUsd  = $price * $amountBtc;
            $type       = $openOrder->type == 0 ? OrderDTO::ORDER_TYPE_BUY : OrderDTO::ORDER_TYPE_SELL;

            /** @var OrderDTO $orderDTO */
            $orderDTO = new OrderDTO($orderId, Ticker::QUADRIGACX, $price, $amountUsd, $amountBtc, $timestamp, $type);

            array_push($result, $orderDTO);
        }

        return $result;
    }
}
