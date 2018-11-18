<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Helper\Okcoin\ApiKeyAuthentication;
use AppBundle\Helper\OkcoinHelper;
use AppBundle\Service\Client\ExternalClientInterface;

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
    public function getTicker():? TickerDTO
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
    public function getBalance():? BalanceDTO
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
    public function placeBuyOrder(float $amount, float $price):? OrderDTO
    {
        /** @var string $apiKey */
        $apiKey = $this->getClient()->getParameters()['api_key'];

        /** @var array $params */
        $params = ['api_key' => $apiKey, 'symbol' => 'btc_usd', 'type'=> 'buy', 'price' => $price, 'amount' => $amount];

        /** @var array $order */
        $order = $this->okcoinHelper->tradeApi($params);

        if($order['result'] != 'true'){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $id         = $order['order_id'];
        $amountUsd  = $price * $amount;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::OKCOIN, $price, $amountUsd, $amount,$timestamp,OrderDTO::ORDER_TYPE_BUY);

        return $orderDTO;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):? OrderDTO
    {
        /** @var string $apiKey */
        $apiKey = $this->getClient()->getParameters()['api_key'];

        /** @var array $params */
        $params = ['api_key' => $apiKey, 'symbol' => 'btc_usd', 'type'=> 'sell', 'price' => $price, 'amount' => $amount];

        /** @var array $order */
        $order = $this->okcoinHelper->tradeApi($params);

        if($order['result'] != 'true'){
            return null;
        }

        $timestamp  = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $id         = $order['order_id'];
        $amountUsd  = $price * $amount;

        /** @var OrderDTO $orderDTO */
        $orderDTO = new OrderDTO($id, Ticker::OKCOIN, $price, $amountUsd, $amount,$timestamp,OrderDTO::ORDER_TYPE_SELL);

        return $orderDTO;
    }

    /**
     * @return OrderDTO[]
     */
    public function getOrders(): array
    {
        /** @var array $orderParams */
        $orderParams = ['current_page' => 1, 'page_length' => 10, 'status' => 0];

        /** @var array $openOrders */
        $openOrders = $this->okcoinHelper->orderHistoryApi($orderParams);

        /** @var OrderDTO[] $result */
        $result = [];

        if(!$openOrders || !array_key_exists('orders', $openOrders)){
            return $result;
        }
        foreach($openOrders['orders'] as $openOrder){
            if($openOrder['status'] == 0 || $openOrder['status'] == 1) {
                $timestamp = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
                $orderId = $openOrder['order_id'];
                $price = $openOrder['price'];
                $amountBtc = $openOrder['amount'];
                $amountUsd = $price * $amountBtc;
                $type = $openOrder['type'] == 'buy' ? OrderDTO::ORDER_TYPE_BUY : OrderDTO::ORDER_TYPE_SELL;

                /** @var OrderDTO $orderDTO */
                $orderDTO = new OrderDTO($orderId, Ticker::OKCOIN, $price, $amountUsd, $amountBtc, $timestamp, $type);

                array_push($result, $orderDTO);
            }
        }

        return $result;
    }
}
