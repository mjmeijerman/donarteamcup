<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * TurnsterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TurnsterRepository extends EntityRepository
{
    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function getBezettePlekken()
    {
        $bezettePlekken = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 0')
            ->getQuery()
            ->getSingleScalarResult();
        return $bezettePlekken;
    }

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function getAantalWachtlijstPlekken()
    {
        $bezettePlekken = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 1')
            ->getQuery()
            ->getSingleScalarResult();
        return $bezettePlekken;
    }

    /**
     * @param $user
     *
     * @return int
     * @throws NonUniqueResultException
     */
    public function getAantalAfgemeldeTurnsters($user)
    {
        $afgemeldeTurnsters = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 1')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
        return $afgemeldeTurnsters;
    }

    /**
     * @param $geboortejaar
     * @param $niveau
     *
     * @return int
     * @throws NonUniqueResultException
     */
    public function getAantalTurnstersPerNiveau($geboortejaar, $niveau)
    {
        $turnsters = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 0')
            ->andWhere('u.geboortejaar = :geboortejaar')
            ->andWhere('u.niveau = :niveau')
            ->setParameters(
                [
                    'geboortejaar' => $geboortejaar,
                    'niveau'       => $niveau,
                ]
            )
            ->getQuery()
            ->getSingleScalarResult();
        return $turnsters;
    }

    /**
     * @param $geboortejaar
     * @param $niveau
     *
     * @return int
     * @throws NonUniqueResultException
     */
    public function getAantalTurnstersWachtlijstPerNiveau($geboortejaar, $niveau)
    {
        $turnsters = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 1')
            ->andWhere('u.geboortejaar = :geboortejaar')
            ->andWhere('u.niveau = :niveau')
            ->setParameters(
                [
                    'geboortejaar' => $geboortejaar,
                    'niveau'       => $niveau,
                ]
            )
            ->getQuery()
            ->getSingleScalarResult();
        return $turnsters;
    }

    /**
     * @param $user
     *
     * @return int
     * @throws NonUniqueResultException
     */
    public function getIngeschrevenTurnsters($user)
    {
        $ingeschrevenTurnsters = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 0')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
        return $ingeschrevenTurnsters;
    }

    /**
     * @param $user
     *
     * @return int
     * @throws NonUniqueResultException
     */
    public function getWachtlijstTurnsters($user)
    {
        $ingeschrevenTurnsters = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 1')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
        return $ingeschrevenTurnsters;
    }

    /**
     * @param $user
     *
     * @return int
     * @throws NonUniqueResultException
     */
    public function getAfgemeldeTurnsters($user)
    {
        $ingeschrevenTurnsters = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.afgemeld = 1')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
        return $ingeschrevenTurnsters;
    }

    /**
     * @param $user
     *
     * @return Turnster[]
     */
    public function getIngeschrevenTurnstersForUser($user)
    {
        $results = $this->createQueryBuilder('u')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 0')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        return $results;
    }

    /**
     * @param $user
     *
     * @return Turnster[]
     */
    public function getWachtlijstTurnstersForUser($user)
    {
        $results = $this->createQueryBuilder('u')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 1')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        return $results;
    }

    /**
     * @param $user
     *
     * @return Turnster[]
     */
    public function getAfgemeldTurnstersForUser($user)
    {
        $results = $this->createQueryBuilder('u')
            ->where('u.afgemeld = 1')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        return $results;
    }

    /**
     * @param $categorie
     * @param $niveau
     *
     * @return Turnster[]
     */
    public function getIngeschrevenTurnstersCatNiveau($categorie, $niveau)
    {
        $ingeschrevenTurnsters = $this->createQueryBuilder('u')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 0')
            ->andWhere('u.categorie = :categorie')
            ->andWhere('u.niveau = :niveau')
            ->orderBy('u.user')
            ->setParameters(
                [
                    'niveau'    => $niveau,
                    'categorie' => $categorie,
                ]
            )
            ->getQuery()
            ->getResult();
        return $ingeschrevenTurnsters;
    }

    /**
     * @param $categorie
     * @param $niveau
     *
     * @return Turnster[]
     */
    public function getWachtlijstTurnstersCatNiveau($categorie, $niveau)
    {
        $ingeschrevenTurnsters = $this->createQueryBuilder('u')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 1')
            ->andWhere('u.categorie = :categorie')
            ->andWhere('u.niveau = :niveau')
            ->orderBy('u.id')
            ->setParameters(
                [
                    'niveau'    => $niveau,
                    'categorie' => $categorie,
                ]
            )
            ->getQuery()
            ->getResult();
        return $ingeschrevenTurnsters;
    }

    /**
     * @return Turnster[]
     */
    public function getGereserveerdePlekken()
    {
        $gereserveerdePlekken = $this->createQueryBuilder('u')
            ->where('u.afgemeld = 0')
            ->andWhere('u.expirationDate IS NOT NULL')
            ->getQuery()
            ->getResult();
        return $gereserveerdePlekken;
    }

    /**
     * @param $limit
     *
     * @return Turnster[]
     */
    public function getWachtlijstPlekken($limit)
    {
        $result = $this->createQueryBuilder('u')
            ->where('u.afgemeld = 0')
            ->andWhere('u.wachtlijst = 1')
            ->orderBy('u.id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function getTijdVol()
    {
        $result = $this->createQueryBuilder('u')
            ->select('u.creationDate')
            ->where('u.wachtlijst = 0')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function getDistinctCatNiv($userId)
    {
        $results = $this->createQueryBuilder('cc')
            ->join('cc.user', 'u')
            ->select('cc.categorie, cc.niveau')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->distinct()
            ->getQuery()
            ->getResult();
        return $results;
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTurnstersOrderedByDayAndVereniging()
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql
            = <<<EOQ
SELECT
  t.id,
  t.categorie,
  t.niveau,
  t.voornaam,
  t.achternaam,
  v.naam as vereniging_naam,
  v.plaats as vereniging_plaats,
  s.wedstrijdnummer
FROM
  turnster t
JOIN
  scores s ON t.score_id = s.id
JOIN
  user u ON t.user_id = u.id
JOIN
  vereniging v ON u.vereniging_id = v.id
WHERE
  t.wachtlijst = 0
AND
  t.afgemeld = 0
ORDER BY
  s.wedstrijddag, t.user_id, s.wedstrijdronde, s.wedstrijdnummer
EOQ;

        $stmt = $connection->executeQuery($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
