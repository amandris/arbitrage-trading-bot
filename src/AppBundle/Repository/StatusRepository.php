<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Status;
use Doctrine\ORM\EntityRepository;
use Experiencia\DktCard\Repository\StatusRepositoryInterface;

/**
 * Class StatusRepository
 * @package AppBundle\Repository
 */
class StatusRepository extends EntityRepository
{
    /**
     * @param $statusId
     * @return Status
     */
    public function findOneById($statusId)
    {
        /** @var Status $status */
        $status = $this->findOneBy(['id' => $statusId]);
        return $status;
    }

    /**
     * @return Status
     */
    public function findStatus()
    {
        $status = $this->findOneBy([]);
        if($status === null){
            $status = new Status();
            $status->setRunning(false);
            $status->setDifferenceUsd(50);
            $status->setStartDate(null);

            $this->save($status);
        }

        return $status;
    }

    /**
     * @param Status $status
     * @return Status
     */
    public function save(Status $status)
    {
        $this->_em->persist($status);
        $this->_em->flush();

        return $status;
    }
}
