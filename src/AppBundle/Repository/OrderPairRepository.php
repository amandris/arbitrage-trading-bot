<?php

namespace AppBundle\Repository;

use AppBundle\Entity\OrderPair;
use Doctrine\ORM\EntityRepository;

/**
 * Class OrderPairRepository
 * @package AppBundle\Repository
 */
class OrderPairRepository extends EntityRepository
{
    /**
     * @param $orderPairId
     * @return OrderPair
     */
    public function findOneById($orderPairId)
    {
        /** @var OrderPair $orderPair */
        $orderPair = $this->findOneBy(['id' => $orderPairId]);
        return $orderPair;
    }

    /**
     * @param OrderPair $orderPair
     * @return OrderPair
     */
    public function save(OrderPair $orderPair)
    {
        $this->_em->persist($orderPair);
        $this->_em->flush();

        return $orderPair;
    }
}
