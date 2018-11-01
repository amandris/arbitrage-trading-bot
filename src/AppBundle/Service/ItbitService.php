<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class ItbitService
 * @package AppBundle\Service
 */
class ItbitService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * ItbitService constructor.
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
            '/markets/XBTUSD/ticker/'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO ('itbit', $responseJson->ask, $responseJson->bid, new \DateTime($responseJson->serverTimeUTC));

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
