<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Service\Client\ExternalClientInterface;

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

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO ('bitstamp', $responseJson->ask, $responseJson->bid);

        return $tickerDTO;
    }
}
