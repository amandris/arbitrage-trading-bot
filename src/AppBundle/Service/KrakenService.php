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
    public function getTicker():TickerDTO
    {
        $response = $this->getClient()->request(
            'GET',
            '/0/public/Ticker?pair=xbtusd'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::KRAKEN, $responseJson->result->XXBTZUSD->a[0], $responseJson->result->XXBTZUSD->b[0], new \DateTime('now', new \DateTimeZone('Europe/Madrid')));

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance(): BalanceDTO
    {
        /** @var array $balance */
        $balance = $this->krakenHelper->queryPrivate('Balance');

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
