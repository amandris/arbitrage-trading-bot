<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;

/**
 * Interface ExchangeServiceInterface
 * @package AppBundle\Service
 */
interface ExchangeServiceInterface
{
    /**
     * @return TickerDTO
     */
    public function getTicker():?TickerDTO;

    /**
     * @return BalanceDTO
     */
    public function getBalance():?BalanceDTO;

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeBuyOrder(float $amount, float $price):?OrderDTO;

    /**
     * @param float $amount
     * @param float $price
     * @return OrderDTO
     */
    public function placeSellOrder(float $amount, float $price):?OrderDTO;

    /**
     * @return OrderDTO[]
     */
    public function getOrders():array;

}
