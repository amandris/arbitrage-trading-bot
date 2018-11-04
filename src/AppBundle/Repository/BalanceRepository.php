<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Balance;
use DateTime;
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
     * @param DateTime $date
     * @return Balance[]
     */
    public function findFirstBalances($date, $limit)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->where('b.created >= :date')
            ->orderBy('b.created', 'ASC')
            ->setParameter('date', $date)
            ->setMaxResults($limit)
            ->getQuery();

        $results = $queryBuilder->getResult();

        return $results;
    }

    /**
     * @param DateTime $date
     * @return Balance[]
     */
    public function findLastBalances($date, $limit)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->where('b.created >= :date')
            ->orderBy('b.created', 'DESC')
            ->setParameter('date', $date)
            ->setMaxResults($limit)
            ->getQuery();

        $results = $queryBuilder->getResult();

        return $results;
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
