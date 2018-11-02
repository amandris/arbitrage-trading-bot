<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Service\Client\ExternalClientInterface;
use DateTime;

/**
 * Class OkcoinService
 * @package AppBundle\Service
 */
class OkcoinService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * OkcoinService constructor.
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
            '/ticker.do?symbol=btc_usd'
        );

        $responseJson = json_decode($response->getBody()->getContents());

        $timestamp = new DateTime();
        $timestamp->setTimestamp($responseJson->date);

        /** @var TickerDTO $tickerDTO */
        $tickerDTO = new TickerDTO (Ticker::OKCOIN, $responseJson->ticker->buy, $responseJson->ticker->sell, $timestamp);

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
