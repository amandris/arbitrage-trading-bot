<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Ticker;
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
     * @param Ticker $ticker
     * @return Ticker
     */
    public function save(Ticker $ticker)
    {
        $this->_em->persist($ticker);
        $this->_em->flush();

        return $ticker;
    }

    /**
     * Delete al rows in table
     */
    public function deleteAll(){
        $query = $this->getEntityManager()->createQuery('DELETE AppBundle:Ticker');
        $query->execute();
    }
}
