<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class Instellingen
 * @package AppBundle\Entity
 */
class InstellingenRepository extends EntityRepository
{
    /**
     * @param \DateTime $datumGeopend
     *
     * @return Instellingen
     * @throws NonUniqueResultException
     */
    public function getTijdVol(\DateTime $datumGeopend)
    {
        $result = $this->createQueryBuilder('u')
            ->where('u.instelling = :tijdVol')
            ->andWhere('u.gewijzigd > :datumGeopend')
            ->setParameters([
                'datumGeopend' => $datumGeopend->format('Y-m-d H:i:s'),
                'tijdVol' => 'tijdVol',
            ])
            ->getQuery()
            ->getOneOrNullResult();
        return $result;
    }
}
