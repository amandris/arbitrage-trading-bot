<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
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
    public function getTicker():TickerDTO
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
    public function getBalance(): BalanceDTO
    {

        /** @var array $balance */
        $balance = $this->bittrexHelper->getBalances();

        /**
         * @var float $usd
         */
        $usd = 0;

        /**
         * @var float $btc
         */
        $btc = 0;

        if($balance != null && count($balance) > 0 ) {
            if(array_key_exists('USD', $balance)){
                $usd = $balance['USD']['Available'];
            }

            if(array_key_exists('BTC', $balance)){
                $btc = $balance['BTC']['Available'];
            }
        }

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::BITTREX, $usd, $btc);

        return $balanceDTO;
    }
}
