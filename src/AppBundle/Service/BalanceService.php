<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\Entity\Balance;
use AppBundle\Repository\BalanceRepository;

/**
 * Class BalanceService
 * @package AppBundle\Service
 */
class BalanceService
{
    /** @var ExchangeServiceInterface[] $exchangeServices */
    private $exchangeServices;

    /** @var BalanceRepository $balanceRepository */
    private $balanceRepository;

    /**
     * BalanceService constructor.
     * @param array $exchangeServices
     * @param BalanceRepository $balanceRepository
     */
    public function __construct(array $exchangeServices, BalanceRepository $balanceRepository)
    {
        $this->exchangeServices = $exchangeServices;
        $this->balanceRepository = $balanceRepository;
    }

    /**
     * @return BalanceDTO[]
     */
    public function getBalancesFromExchanges()
    {
        /** @var BalanceDTO[] $balanceDTOs */
        $balanceDTOs = [];

        foreach($this->exchangeServices as $name => $exchangeService){
            $parameters = $exchangeService->getClient()->getParameters();
            if($parameters['enable']) {
                $balanceDTO = $exchangeService->getBalance();
                if($balanceDTO) {
                    array_push($balanceDTOs, $balanceDTO);
                }
            }
        }

        $this->balanceRepository->deleteAll();

        $now = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));

        foreach ($balanceDTOs as $balanceDTO) {
            $balance = new Balance();
            $balance->setName($balanceDTO->getName());
            $balance->setUsd($balanceDTO->getUsd());
            $balance->setBtc($balanceDTO->getBtc());
            $balance->setCreated($now);

            $this->balanceRepository->save($balance);
        }

        return $balanceDTOs;
    }

    /**
     * @return array
     */
    public function getUsdBalancesFormatted()
    {
        /** @var Balance[] $balances */
        $balances = $this->balanceRepository->findAll();

        /** @var array $result */
        $result = [];

        foreach ($balances as $balance){
            $result[$balance->getName()] = $balance->getUsd();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getBtcBalancesFormatted()
    {
        /** @var Balance[] $balances */
        $balances = $this->balanceRepository->findAll();

        /** @var array $result */
        $result = [];

        foreach ($balances as $balance){
            $result[$balance->getName()] = $balance->getBtc();
        }

        return $result;
    }
}
