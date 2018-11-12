<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
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

        $timestamp = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
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
}
