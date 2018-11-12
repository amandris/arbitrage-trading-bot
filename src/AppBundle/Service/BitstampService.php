<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\BitstampHelper;
use AppBundle\Service\Client\ExternalClientInterface;
use DateTime;

/**
 * Class BitstampService
 * @package AppBundle\Service
 */
class BitstampService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var BitstampHelper $bitstampHelper
     */
    private $bitstampHelper;

    /**
     * BitstampService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->bitstampHelper = new BitstampHelper( $parameters['api_key'],
                                                    $parameters['api_secret'],
                                                    $parameters['client_id'],
                                            $parameters['base_uri'].'/');
    }

    /**
     * @return TickerDTO
     */
    public function getTicker():TickerDTO
    {
        $response = $this->getClient()->request(
            'GET',
            '/ticker/btcusd/'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        $timestamp = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp->setTimestamp($responseJson->timestamp);

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::BITSTAMP, $responseJson->ask, $responseJson->bid, $timestamp);

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance(): BalanceDTO
    {
        /** @var array $balance */
        $balance = $this->bitstampHelper->balance();

        /** @var float $usd */
        $usd = $balance['btc_available'];

        /** @var float $btc */
        $btc = $balance['usd_available'];

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::BITSTAMP, $usd, $btc);

        return $balanceDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeBuyOrder(float $amount, float $price):OrderDTO
    {
        /** @var array $order */
        $order = $this->bitstampHelper->buyBTC($amount, $price);

        if(array_key_exists('error', $order)){
            return null;
        }

        $timestamp  = new \DateTime($order['datetime'], new \DateTimeZone('Europe/Madrid'));
        $id         = $order['id'];
        $price      = $order['price'];
        $amountBtc  = $order['amount'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::BITSTAMP, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_BUY);

        return $orderDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):OrderDTO
    {
        /** @var array $order */
        $order = $this->bitstampHelper->sellBTC($amount, $price);

        if(array_key_exists('error', $order)){
            return null;
        }

        $timestamp  = new \DateTime($order['datetime'], new \DateTimeZone('Europe/Madrid'));
        $id         = $order['id'];
        $price      = $order['price'];
        $amountBtc  = $order['amount'];
        $amountUsd  = $price * $amountBtc;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::BITSTAMP, $price, $amountUsd, $amountBtc,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }


}
