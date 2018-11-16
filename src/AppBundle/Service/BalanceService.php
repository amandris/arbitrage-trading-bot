<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\Entity\Status;
use AppBundle\Repository\BalanceRepository;
use AppBundle\Repository\StatusRepository;

/**
 * Class BalanceService
 * @package AppBundle\Service
 */
class BalanceService
{
    /** @var ExchangeServiceInterface[] $exchangeServices */
    private $exchangeServices;

    /** @var StatusRepository $statusRepository */
    private $statusRepository;

    /** @var BalanceRepository $balanceRepository */
    private $balanceRepository;

    /**
     * BalanceService constructor.
     * @param array $exchangeServices
     * @param StatusRepository $statusRepository
     * @param BalanceRepository $balanceRepository
     */
    public function __construct(array $exchangeServices, StatusRepository $statusRepository, BalanceRepository $balanceRepository)
    {
        $this->exchangeServices = $exchangeServices;
        $this->statusRepository = $statusRepository;
        $this->balanceRepository = $balanceRepository;
    }

    /**
     * @return BalanceDTO[]
     */
    public function getBalances()
    {
        /** @var BalanceDTO[] $result */
        $result = [];

        foreach($this->exchangeServices as $name => $exchangeService){
            $parameters = $exchangeService->getClient()->getParameters();
            if($parameters['enable']) {
                $balanceDTO = $exchangeService->getBalance();
                if($balanceDTO) {
                    array_push($result, $balanceDTO);
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFormattedBalances()
    {
        $resultFirst    = [];
        $resultLast     = [];
        $exchangeNames  = [];
        $result         = [];

        /** @var Status $status */
        $status = $this->statusRepository->findStatus();

        if($status->isRunning() === true && $status->getStartDate()){
            $firstBalances = $this->balanceRepository->findFirstBalances($status->getStartDate(), 12);
            $lastBalances = $this->balanceRepository->findLastBalances($status->getStartDate(),12);

            foreach($firstBalances as $firstBalance){
                if(!array_key_exists($firstBalance->getName(), $resultFirst)){
                    $resultFirst[$firstBalance->getName()] = ['usd'=>$firstBalance->getUsd(), 'btc'=>number_format($firstBalance->getBtc(), 8)];
                    array_push($exchangeNames, $firstBalance->getName());
                }
            }

            foreach($lastBalances as $lastBalance){
                if(!array_key_exists($lastBalance->getName(), $resultLast)) {
                    $resultLast[$lastBalance->getName()] = ['usd' => $lastBalance->getUsd(), 'btc' => number_format($lastBalance->getBtc() ,8)];
                    array_push($exchangeNames, $lastBalance->getName());
                }
            }
        }

        $exchangeNames = array_unique($exchangeNames);

        foreach ($exchangeNames as $exchangeName){
            $firstBalance = array_key_exists($exchangeName, $resultFirst) ? $resultFirst[$exchangeName] : ['usd' => '', 'btc' => ''];
            $lastBalance = array_key_exists($exchangeName, $resultLast) ? $resultLast[$exchangeName] : ['usd' => '', 'btc' => ''];
            array_push($result, ['name' => $exchangeName , 'first' => $firstBalance , 'last' => $lastBalance]);
        }

        return $result;
    }
}
