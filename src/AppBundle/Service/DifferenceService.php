<?php

namespace AppBundle\Service;

use AppBundle\Entity\Difference;
use AppBundle\Entity\Status;
use AppBundle\Repository\DifferenceRepository;
use AppBundle\Repository\StatusRepository;

/**
 * Class DifferenceService
 * @package AppBundle\Service
 */
class DifferenceService
{
    /** @var StatusRepository $statusRepository */
    private $statusRepository;

    /** @var DifferenceRepository $differenceRepository */
    private $differenceRepository;

    /**
     * DifferenceService constructor.
     * @param StatusRepository $statusRepository
     * @param DifferenceRepository $differenceRepository
     */
    public function __construct(StatusRepository $statusRepository, DifferenceRepository $differenceRepository)
    {
        $this->statusRepository = $statusRepository;
        $this->differenceRepository = $differenceRepository;
    }

    /**
     * @return array
     */
    public function getFormattedDifferences()
    {
        $exchangeNames  = [];
        $result         = [];

        /** @var Status $status */
        $status = $this->statusRepository->findStatus();

        if($status->getStartDate()){
            $lastDifferences = $this->differenceRepository->findLastDifferences($status->getStartDate(),132);

            foreach($lastDifferences as $lastDifference){
                if(!in_array($lastDifference->getExchangeNames(), $exchangeNames)){
                    array_push($result, $lastDifference);
                    array_push($exchangeNames, $lastDifference->getExchangeNames());
                }
            }
        }

        $compare = function(Difference $diff1, Difference $diff2)
        {
            if ($diff1->getDifference() == $diff2->getDifference()) {
                return 0;
            }
            return ($diff1->getDifference() < $diff2->getDifference()) ? 1 : -1;
        };

        usort($result, $compare);

        return $result;
    }
}
