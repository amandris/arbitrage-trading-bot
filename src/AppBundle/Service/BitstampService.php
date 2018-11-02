<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Service\Client\ExternalClientInterface;
use DateTime;

/**
 * Class BitstampService
 * @package AppBundle\Service
 */
class BitstampService extends ClientAwareService implements ExchangeServiceInterface
{

    /**
     * BitstampService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);
    }

    /**
     * @return TickerDTO
     */
    public function getTicker():TickerDTO
    {
        $response = $this->getClient()->request(
            'GET',
            '/api/v2/ticker/btcusd/'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        $timestamp = new DateTime();
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
        // TODO: Implement getBalance() method.
    }
}
