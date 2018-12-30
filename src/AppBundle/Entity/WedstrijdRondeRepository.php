<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * @package AppBundle\Entity
 */
class WedstrijdRondeRepository extends EntityRepository
{
    public function getDagen()
    {
        return $this->createQueryBuilder('wr')
            ->select('wr.dag')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function getWedstrijdrondesPerDag(string $dag)
    {
        return $this->createQueryBuilder('wr')
            ->select('wr')
            ->andWhere('wr.dag = :dag')
            ->setParameter('dag', $dag)
            ->orderBy('wr.ronde')
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}
