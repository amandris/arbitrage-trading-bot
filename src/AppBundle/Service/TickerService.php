<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\TickerDTO;

/**
 * Class TickerService
 * @package AppBundle\Service
 */
class TickerService
{
    /** @var ExchangeServiceInterface[] $exchangeServices */
    private $exchangeServices;

    /**
     * TickerService constructor.
     * @param array $exchangeServices
     */
    public function __construct(array $exchangeServices)
    {
        $this->exchangeServices = $exchangeServices;
    }

    /**
     * @return TickerDTO[]
     */
    public function getTickers()
    {
        /** @var TickerDTO[] $result */
        $result = [];

        foreach($this->exchangeServices as $name => $exchangeService){
            $parameters = $exchangeService->getClient()->getParameters();
            if($parameters['enable'] && isset($parameters['api_key']) &&$parameters['api_key'] !== '' ) {
                $tickerDTO = $exchangeService->getTicker();
                if($tickerDTO != null) {
                    array_push($result, $tickerDTO);
                }
            }
        }

        return $result;
    }
}
