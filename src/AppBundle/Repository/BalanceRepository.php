<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Balance;
use Doctrine\ORM\EntityRepository;

/**
 * Class BalanceRepository
 * @package AppBundle\Repository
 */
class BalanceRepository extends EntityRepository
{
    /**
     * @param $balanceId
     * @return Balance
     */
    public function findOneById($balanceId)
    {
        /** @var Balance $balance */
        $balance = $this->findOneBy(['id' => $balanceId]);
        return $balance;
    }

    /**
     * @param Balance $balance
     * @return Balance
     */
    public function save(Balance $balance)
    {
        $this->_em->persist($balance);
        $this->_em->flush();

        return $balance;
    }
}
