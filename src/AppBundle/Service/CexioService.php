<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\CexioHelper;
use AppBundle\Service\Client\ExternalClientInterface;
use DateTime;

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
    public function getTicker():TickerDTO
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
    public function getBalance(): BalanceDTO
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
    public function placeBuyOrder(float $amount, float $price): OrderDTO
    {
        // TODO: Implement placeBuyOrder() method.
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price): OrderDTO
    {
        // TODO: Implement placeSellOrder() method.
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        // TODO: Implement getOrders() method.
    }
}
