<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\Okcoin\ApiKeyAuthentication;
use AppBundle\Helper\OkcoinHelper;
use AppBundle\Service\Client\ExternalClientInterface;
use DateTime;

/**
 * Class OkcoinService
 * @package AppBundle\Service
 */
class OkcoinService extends ClientAwareService implements ExchangeServiceInterface
{
    /**
     * @var OkcoinHelper $okcoinHelper
     */
    private $okcoinHelper;

    /**
     * OkcoinService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        parent::__construct($client);

        /** @var array $parameters */
        $parameters = $client->getParameters();

        $this->okcoinHelper = new OkcoinHelper( new ApiKeyAuthentication($parameters['api_key'], $parameters['api_secret']));
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
        $apiKey = $this->getClient()->getParameters()['api_key'];
        $params = array('api_key' => $apiKey);

        /** @var \stdClass $balance */
        $balance = $this->okcoinHelper->userinfoApi($params);

        /** @var float $usd */
        $usd = $balance->info->funds->free->usd;

        /** @var float $btc */
        $btc = $balance->info->funds->free->btc;

        /** @var BalanceDTO $balanceDTO */
        $balanceDTO = new BalanceDTO ( Ticker::OKCOIN, $usd, $btc);

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
