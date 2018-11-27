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
     * @param string $exchange
     * @return Balance
     */
    public function findBalanceByExchange($exchange)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->where('b.name = :exchange')
            ->orderBy('b.created', 'DESC')
            ->setParameter('exchange', $exchange)
            ->getQuery();

        $results = $queryBuilder->getResult();

        if(count($results) > 0){
            return $results[0];
        }
        return null;
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

    /**
     * Delete al rows in table
     */
    public function deleteAll(){
        $query = $this->getEntityManager()->createQuery('DELETE AppBundle:Balance');
        $query->execute();
    }
}
