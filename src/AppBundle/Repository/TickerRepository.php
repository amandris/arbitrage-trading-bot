<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Ticker;
use DateTime;
use Doctrine\ORM\EntityRepository;

/**
 * Class TickerRepository
 * @package AppBundle\Repository
 */
class TickerRepository extends EntityRepository
{
    /**
     * @param $tickerId
     * @return Ticker
     */
    public function findOneById($tickerId)
    {
        /** @var Ticker $ticker */
        $ticker = $this->findOneBy(['id' => $tickerId]);
        return $ticker;
    }

    /**
     * @param DateTime $date
     * @return Ticker[]
     */
    public function findFirstTickers($date, $limit)
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->where('t.created >= :date')
            ->orderBy('t.created', 'ASC')
            ->setParameter('date', $date)
            ->setMaxResults($limit)
            ->getQuery();

        $results = $queryBuilder->getResult();

        return $results;
    }

    /**
     * @param DateTime $date
     * @return Ticker[]
     */
    public function findLastTickers($date, $limit)
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->where('t.created >= :date')
            ->orderBy('t.created', 'DESC')
            ->setParameter('date', $date)
            ->setMaxResults($limit)
            ->getQuery();

        $results = $queryBuilder->getResult();

        return $results;
    }

    /**
     * @param Ticker $ticker
     * @return Ticker
     */
    public function save(Ticker $ticker)
    {
        $this->_em->persist($ticker);
        $this->_em->flush();

        return $ticker;
    }
}
