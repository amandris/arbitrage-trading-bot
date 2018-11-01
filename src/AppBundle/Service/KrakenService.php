<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class KrakenService
 * @package AppBundle\Service
 */
class KrakenService extends ClientAwareService implements ExchangeServiceInterface
{

    /**
     * KrakenService constructor.
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
            '/public/Ticker?pair=xbtusd'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO ('kraken', $responseJson->result->XXBTZUSD->a[0], $responseJson->result->XXBTZUSD->b[0], new \DateTime('now'));

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
