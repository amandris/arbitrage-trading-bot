<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Interface ExchangeServiceInterface
 * @package AppBundle\Service
 */
interface ExchangeServiceInterface
{
    /**
     * @return TickerDTO
     */
    public function getTicker():TickerDTO;

    /**
     * @return BalanceDTO
     */
    public function getBalance():BalanceDTO;

    /**
     * @return ExternalClientInterface
     */
    public function getClient(): ExternalClientInterface;
}
