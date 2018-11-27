<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Difference;
use Doctrine\ORM\EntityRepository;

/**
 * Class DifferenceRepository
 * @package AppBundle\Repository
 */
class DifferenceRepository extends EntityRepository
{
    /**
     * @param $differenceId
     * @return Difference
     */
    public function findOneById($differenceId)
    {
        /** @var Difference $difference */
        $difference = $this->findOneBy(['id' => $differenceId]);
        return $difference;
    }

    /**
     * @param $date
     * @param $limit
     * @return Difference[]
     */
    public function findLastDifferences($date, $limit)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.created >= :date')
            ->orderBy('d.created', 'DESC')
            ->setParameter('date', $date)
            ->setMaxResults($limit)
            ->getQuery();

        $results = $queryBuilder->getResult();

        return $results;
    }

    /**
     * @param $date
     * @param $thresholdUsd
     * @return Difference[]
     */
    public function findLastDifferencesGreaterThan($date, $thresholdUsd)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.created >= :date')
            ->andWhere('d.difference >= :thresholdUsd')
            ->orderBy('d.difference', 'DESC')
            ->setParameter('date', $date)
            ->setParameter('thresholdUsd', $thresholdUsd)
            ->getQuery();

        $results = $queryBuilder->getResult();

        return $results;
    }

    /**
     * @param Difference $difference
     * @return Difference
     */
    public function save(Difference $difference)
    {
        $this->_em->persist($difference);
        $this->_em->flush();

        return $difference;
    }

    /**
     * Delete al rows in table
     */
    public function deleteAll(){
        $query = $this->getEntityManager()->createQuery('DELETE AppBundle:Difference');
        $query->execute();
    }
}
