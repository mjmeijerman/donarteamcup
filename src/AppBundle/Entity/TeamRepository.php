<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TeamRepository extends EntityRepository
{
    public function findByTeamName($name): ?Team
    {
        return $this->createQueryBuilder('t')
            ->where('t.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
