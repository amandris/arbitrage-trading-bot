<?php

namespace AppBundle\Service;

use AppBundle\DataTransferObject\BalanceDTO;

/**
 * Class BalanceService
 * @package AppBundle\Service
 */
class BalanceService
{
    /** @var ExchangeServiceInterface[] $exchangeServices */
    private $exchangeServices;

    /**
     * BalanceService constructor.
     * @param array $exchangeServices
     */
    public function __construct(array $exchangeServices)
    {
        $this->exchangeServices = $exchangeServices;
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
                array_push($result, $balanceDTO);
            }
        }

        return $result;
    }
}
