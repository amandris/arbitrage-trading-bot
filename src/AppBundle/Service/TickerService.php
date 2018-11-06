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
                array_push($result, $tickerDTO);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFormattedTickers()
    {
        /** @var Status $status */
        $status = $this->statusRepository->findStatus();

        $resultFirst    = [];
        $resultLast     = [];
        $exchangeNames  = [];
        $result         = [];

        if($status->getStartDate()){
            $firstTickers = $this->tickerRepository->findFirstTickers($status->getStartDate(), 12);
            $lastTickers = $this->tickerRepository->findLastTickers($status->getStartDate(),12);

            foreach($firstTickers as $firstTicker){
                if(!array_key_exists($firstTicker->getName(), $resultFirst)){
                    $resultFirst[$firstTicker->getName()] = ['ask'=>$firstTicker->getAsk(), 'bid'=>$firstTicker->getBid()];
                    array_push($exchangeNames, $firstTicker->getName());
                }
            }

            foreach($lastTickers as $lastTicker){
                if(!array_key_exists($lastTicker->getName(), $resultLast)) {
                    $resultLast[$lastTicker->getName()] = ['ask' => $lastTicker->getAsk(), 'bid' => $lastTicker->getBid()];
                    array_push($exchangeNames, $lastTicker->getName());
                }
            }
        }

        $exchangeNames = array_unique($exchangeNames);

        foreach ($exchangeNames as $exchangeName){
            $firstTicker = array_key_exists($exchangeName, $resultFirst) ? $resultFirst[$exchangeName] : ['ask' => '', 'bid' => ''];
            $lastTicker = array_key_exists($exchangeName, $resultLast) ? $resultLast[$exchangeName] : ['ask' => '', 'bid' => ''];
            array_push($result, ['name' => $exchangeName , 'first' => $firstTicker , 'last' => $lastTicker]);
        }

        return $result;
    }
}
