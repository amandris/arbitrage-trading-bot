<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\ItbitHelper;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class ItbitService
 * @package AppBundle\Service
 */
class ItbitService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var ItbitHelper $itbitHelper
     */
    private $itbitHelper;

    /**
     * ItbitService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->itbitHelper = new ItbitHelper(   $parameters['api_secret'],
                                                $parameters['api_key'],
                                                $parameters['user_id'],
                                                $parameters['base_uri'].'/');
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
        $tickerDTO = new TickerDTO (Ticker::ITBIT, $responseJson->ask, $responseJson->bid, new \DateTime($responseJson->serverTimeUTC, new \DateTimeZone('Europe/Madrid')));

        return $tickerDTO;
    }

    /**
     * @return BalanceDTO
     */
    public function getBalance(): BalanceDTO
    {
        $responseJson = $this->itbitHelper->wallet()[0];

        /** @var float $usd */
        $usd = 0;

        /** @var float $btc */
        $btc = 0;

        foreach($responseJson->balances as $balance){
            if($balance->currency === 'USD'){
                $usd = $balance->availableBalance;
            }
            if($balance->currency === 'XBT'){
                $btc = $balance->availableBalance;
            }
            break;
        }

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::ITBIT, $usd, $btc);

        return $balanceDTO;
    }
}
