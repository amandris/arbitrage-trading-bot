<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Status;
use AppBundle\Entity\Ticker;
use AppBundle\Repository\StatusRepository;
use AppBundle\Repository\TickerRepository;

/**
 * Class TickerService
 * @package AppBundle\Service
 */
class TickerService
{
    /** @var ExchangeServiceInterface[] $exchangeServices */
    private $exchangeServices;

    /** @var StatusRepository $statusRepository */
    private $statusRepository;

    /** @var TickerRepository $tickerRepository */
    private $tickerRepository;

    /**
     * TickerService constructor.
     * @param array $exchangeServices
     * @param StatusRepository $statusRepository
     * @param TickerRepository $tickerRepository
     */
    public function __construct(array $exchangeServices, StatusRepository $statusRepository, TickerRepository $tickerRepository)
    {
        $this->exchangeServices = $exchangeServices;
        $this->statusRepository = $statusRepository;
        $this->tickerRepository = $tickerRepository;
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
