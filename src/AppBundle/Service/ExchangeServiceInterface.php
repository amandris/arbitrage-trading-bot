<?php

namespace AppBundle\Service;

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
    public function getTicker():TickerDTO;
}
