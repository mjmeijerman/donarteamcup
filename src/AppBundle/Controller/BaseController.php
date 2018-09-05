<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Instellingen;
use AppBundle\Entity\JuryIndeling;
use AppBundle\Entity\Jurylid;
use AppBundle\Entity\Scores;
use AppBundle\Entity\SendMail;
use AppBundle\Entity\TijdSchema;
use AppBundle\Entity\ToegestaneNiveaus;
use AppBundle\Entity\Turnster;
use AppBundle\Entity\User;
use AppBundle\Entity\Vereniging;
use AppBundle\Entity\Voorinschrijving;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Content;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank as EmptyConstraint;

define('EURO', chr(128));

class BaseController extends Controller
{

    const OPENING_INSCHRIJVING = 'Opening inschrijving';
    const OPENING_UPLOADEN_VLOERMUZIEK = 'Opening uploaden vloermuziek';
    const SLUITING_INSCHRIJVING_TURNSTERS = 'Sluiting inschrijving turnsters';
    const SLUITING_INSCHRIJVING_JURYLEDEN = 'Sluiting inschrijving juryleden';
    const SLUITING_UPLOADEN_VLOERMUZIEK = 'Sluiting uploaden vloermuziek';
    const FACTUUR_BEKIJKEN_TOEGESTAAN = 'Factuur publiceren';
    const UITERLIJKE_BETAALDATUM_FACTUUR = 'Uiterlijke betaaldatum';
    const MAX_AANTAL_TURNSTERS = 'Max aantal turnsters';
    const EMPTY_RESULT = 'Klik om te wijzigen';
    const BEDRAG_PER_TURNSTER = 16.50;
    const JURY_BOETE_BEDRAG = 50;
    const AANTAL_TURNSTERS_PER_JURY = 10;
    const DATUM_HBC = '9 & 10 juni 2018';
    const LOCATIE_HBC = 'Sporthal Overbosch';
    const REKENINGNUMMER = 'NL81 INGB 000 007 81 99';
    const REKENING_TNV = 'Gymnastiekver. Donar';

    protected $sponsors = [];
    protected $menuItems = [];
    protected $aantalVerenigingen;
    protected $aantalTurnsters;
    protected $aantalWachtlijst;
    protected $aantalJury;

    protected function getFactuurNummer($user)
    {
        return ('HDC' . date('Y', time()) . '-' . $user->getId());
    }

    protected function getVerenigingen()
    {
        $verenigingen = [];
        /** @var Vereniging[] $results */
        $results = $this->getDoctrine()
            ->getRepository('AppBundle:Vereniging')
            ->findBy(
                [],
                ['naam' => 'ASC']
            );
        foreach ($results as $result) {
            $verenigingen[] = $result->getAll();
        }
        return $verenigingen;
    }

    /**
     * @param            $juryDagData
     * @param Jurylid    $jurylid
     */
    protected function setJurylidBeschikbareDagenFromPostData($juryDagData, Jurylid $jurylid)
    {
        if (strtolower($juryDagData) == 'za') {
            $jurylid->setZaterdag(true);
            $jurylid->setZondag(false);
        } elseif (strtolower($juryDagData) == 'zo') {
            $jurylid->setZaterdag(false);
            $jurylid->setZondag(true);
        } elseif (strtolower($juryDagData) == 'zazo') {
            $jurylid->setZaterdag(true);
            $jurylid->setZondag(true);
        }
    }

    /**
     * @param bool|false $fieldname
     * @return array
     */
    protected function getOrganisatieInstellingen($fieldname = false)
    {
        $instellingen = array();
        if (!$fieldname) {
            $instellingKeys = array(
                self::OPENING_INSCHRIJVING,
                self::SLUITING_INSCHRIJVING_TURNSTERS,
                self::SLUITING_INSCHRIJVING_JURYLEDEN,
                self::OPENING_UPLOADEN_VLOERMUZIEK,
                self::SLUITING_UPLOADEN_VLOERMUZIEK,
                self::FACTUUR_BEKIJKEN_TOEGESTAAN,
                self::UITERLIJKE_BETAALDATUM_FACTUUR,
                self::MAX_AANTAL_TURNSTERS,
            );
        } else {
            $instellingKeys = array($fieldname);
        }
        foreach ($instellingKeys as $key) {
            $result = $this->getDoctrine()
                ->getRepository('AppBundle:Instellingen')
                ->findBy(
                    array('instelling' => $key),
                    array('gewijzigd' => 'DESC')
                );
            if (count($result) > 0) {
                /** @var Instellingen $result */
                $result = $result[0];
            }
            if ($key == self::MAX_AANTAL_TURNSTERS) {
                $instellingen[$key] = ($result) ? $result->getAantal() : self::EMPTY_RESULT;
            } else {
                $instellingen[$key] = ($result) ? $result->getDatum() : self::EMPTY_RESULT;
                if ($result) {
                    $instellingen[$key] = $instellingen[$key]->format('d-m-Y H:i');
                }
            }
        }
        return $instellingen;
    }

    protected function usedVoorinschrijvingsToken($token)
    {
        /** @var Voorinschrijving $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Voorinschrijving')
            ->findOneBy(
                array('token' => $token)
            );
        $result->setUsedAt(new \DateTime('now'));
        $this->addToDB($result);
    }

    protected function isKeuzeOefenstof($geboorteJaar)
    {
        $leeftijd = (date('Y', time())-$geboorteJaar);
        if ($leeftijd >= 13) {
            return true;
        }
        return false;
    }

    protected function checkVoorinschrijvingsToken($token, Session $session = null)
    {
        if ($token === null) {
            return false;
        } elseif ($session == null) {
            return false;
        } elseif ($token == $session->get('token')) {
            return true;
        } else {
            /** @var Voorinschrijving $result */
            $result = $this->getDoctrine()
                ->getRepository('AppBundle:Voorinschrijving')
                ->findOneBy(
                    array('token' => $token)
                );
            if ($result) {
                if ($result->getUsedAt() === null) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function updateGereserveerdePlekken()
    {
        /** @var Turnster[] $gereserveerdePlekken */
        $gereserveerdePlekken = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->getGereserveerdePlekken();
        foreach ($gereserveerdePlekken as $gereserveerdePlek) {
            if ($gereserveerdePlek->getExpirationDate() < new \DateTime('now')) {
                $this->removeFromDB($gereserveerdePlek);
            }
        }
        $sluitingInschrijving = $this->getOrganisatieInstellingen(self::SLUITING_INSCHRIJVING_TURNSTERS);
        if (strtotime($sluitingInschrijving[self::SLUITING_INSCHRIJVING_TURNSTERS]) > time()) {
            /** @var Turnster[] $wachtlijstPlekken */
            $wachtlijstPlekken = $this->getDoctrine()->getRepository('AppBundle:Turnster')
                ->getWachtlijstPlekken($this->getVrijePlekken());
            foreach ($wachtlijstPlekken as $wachtlijstPlek) {
                $wachtlijstPlek->setWachtlijst(false);
                $this->addToDB($wachtlijstPlek);
            }
        }
    }

    protected function getBeschikbareDag($juryObject)
    {
        /** @var Jurylid $juryObject */
        if ($juryObject->getZaterdag() && $juryObject->getZondag()) {
            return 'ZaZo';
        } elseif ($juryObject->getZaterdag()) {
            return 'Za';
        } elseif ($juryObject->getZondag()) {
            return 'Zo';
        } else {
            return 'Geen';
        }
    }

    protected function getToegestaneNiveaus()
    {
        $toegestaneNiveaus = [];
        $repo = $this->getDoctrine()->getRepository('AppBundle:ToegestaneNiveaus');
        /** @var ToegestaneNiveaus[] $results */
        if ($this->getUser() && $this->getUser()->getRole() == 'ROLE_ORGANISATIE') {
            $results = $repo->findAll();
        } else {
            $results = $repo->findBy([
                'uitslagGepubliceerd' => 1,
            ]);
        }
        foreach ($results as $result) {
            /** @var ToegestaneNiveaus[] $results */
            $toegestaneNiveaus[$result->getCategorie()][$result->getId()] = [
                'niveau' => $result->getNiveau(),
                'uitslagGepubliceerd' => $result->getUitslagGepubliceerd(),
            ];
        }

        return $toegestaneNiveaus;
    }

    protected function checkIfNiveauToegestaan($categorie, $niveau)
    {
        /** @var ToegestaneNiveaus $result */
        $result = $this->getDoctrine()->getRepository("AppBundle:ToegestaneNiveaus")
            ->findOneBy([
                'categorie' => $categorie,
                'niveau' => $niveau,
            ]);
        if (!$result) {
            return false;
        }
        if (($this->getUser() && $this->getUser()->getRole() == 'ROLE_ORGANISATIE') ||
            $result->getUitslagGepubliceerd()) {
            return true;
        }
        return false;
    }

    protected function getCategorien()
    {
        return [
            'Voorinstap',
            'Instap',
            'Pupil 1',
            'Pupil 2',
            'Jeugd 1',
            'Jeugd 2',
            'Junior',
            'Senior',
        ];
    }

    protected function getGroepen()
    {
        return [
            'Voorinstap' => ['N2', 'N3', 'D1', 'D2'],
            'Instap' => ['N2', 'N3', 'D1', 'D2'],
            'Pupil 1' => ['N3', 'D1', 'D2'],
            'Pupil 2' => ['N3', 'D1', 'D2'],
            'Jeugd 1' => ['N4', 'D1', 'D2'],
            'Jeugd 2' => ['Div. 3', 'Div. 4', 'Div. 5'],
            'Junior' => ['Div. 3', 'Div. 4', 'Div. 5'],
            'Senior' => ['Div. 3', 'Div. 4', 'Div. 5'],
        ];
    }

    protected function getCategorie($geboorteJaar)
    {
        $leeftijd = (date('Y', time())-$geboorteJaar);
        if ($leeftijd < 8) {
            return '';
        } elseif ($leeftijd == 8) {
            return 'Voorinstap';
        } elseif ($leeftijd == 9) {
            return 'Instap';
        } elseif ($leeftijd == 10) {
            return 'Pupil 1';
        } elseif ($leeftijd == 11) {
            return 'Pupil 2';
        } elseif ($leeftijd == 12) {
            return 'Jeugd 1';
        } elseif ($leeftijd == 13) {
            return 'Jeugd 2';
        } elseif ($leeftijd == 14 || $leeftijd == 15) {
            return 'Junior';
        } else {
            return 'Senior';
        }
    }

    protected function getGeboortejaarFromCategorie($categorie)
    {
        switch ($categorie) {
            case 'Voorinstap': return date('Y', time()) - 8;
            case 'Instap': return date('Y', time()) - 9;
            case 'Pupil 1': return date('Y', time()) - 10;
            case 'Pupil 2': return date('Y', time()) - 11;
            case 'Jeugd 1': return date('Y', time()) - 12;
            case 'Jeugd 2': return date('Y', time()) - 13;
            case 'Junior': return [date('Y', time()) - 14, date('Y', time()) - 15];
            case 'Senior':
                $geboortejaren = [];
                for ($i = 16; $i < 60; $i++) {
                    $geboortejaren[] = date('Y', time()) - $i;
                }
                return $geboortejaren;
        }
    }

    protected function getAvailableNiveaus($geboorteJaar)
    {
        $leeftijd = (date('Y', time())-$geboorteJaar);
        if ($leeftijd < 8) {
            return [];
        } elseif ($leeftijd == 8 || $leeftijd == 9) {
            return ['N2', 'N3', 'D1', 'D2'];
        } elseif ($leeftijd == 10 || $leeftijd == 11) {
            return ['N3', 'D1', 'D2'];
        } elseif ($leeftijd == 12) {
            return ['N4', 'D1', 'D2'];
        } else {
            return ['Div. 3', 'Div. 4', 'Div. 5'];
        }
    }

    protected function getGeboorteJaren()
    {
        $geboorteJaren = [];
        for ($i = (date('Y', time())-8); $i >= 1950 ; $i--) {
            $geboorteJaren[] = $i;
        }
        return $geboorteJaren;
    }

    protected function getVrijePlekken()
    {
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Turnster')
            ->getBezettePlekken();
        $maxPlekken = $this->getOrganisatieInstellingen(self::MAX_AANTAL_TURNSTERS);
        if ($maxPlekken[self::MAX_AANTAL_TURNSTERS] - $result < 0) {
            return 0;
        }
        return ($maxPlekken[self::MAX_AANTAL_TURNSTERS] - $result);
    }

    protected function getTijdVol()
    {
        $datumGeopend = 0;
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Instellingen')
            ->findBy(
                array('instelling' => self::OPENING_INSCHRIJVING),
                array('gewijzigd' => 'DESC')
            );
        /** @var Instellingen $datumGeopend */
        if (count($result) > 0) {
            $datumGeopend = $result[0];
        }

        /** @var Instellingen $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Instellingen')
            ->getTijdVol($datumGeopend->getDatum());
        if ($result) {
            return $result->getDatum();
        } else {
            $result = $this->getDoctrine()
                ->getRepository('AppBundle:Turnster')
                ->getTijdVol();
            $instelling = new Instellingen();
            $instelling->setInstelling('tijdVol');
            $instelling->setGewijzigd(new \DateTime('now'));
            $instelling->setDatum($result[0]['creationDate']);
            $this->addToDB($instelling);
            $result = $this->getDoctrine()
                ->getRepository('AppBundle:Instellingen')
                ->getTijdVol($datumGeopend->getDatum());
            return $result->getDatum();
        }
    }

    protected function inschrijvingToegestaan($token = null, Session $session = null)
    {
        $instellingGeopend = $this->getOrganisatieInstellingen(self::OPENING_INSCHRIJVING);
        $instellingGesloten = $this->getOrganisatieInstellingen(self::SLUITING_INSCHRIJVING_TURNSTERS);
        if ((time() > strtotime($instellingGeopend[self::OPENING_INSCHRIJVING]) &&
                time() < strtotime($instellingGesloten[self::SLUITING_INSCHRIJVING_TURNSTERS])) ||
            $this->checkVoorinschrijvingsToken($token, $session)
        ) {
            return true;
        }
        return false;
    }

    protected function isAfterOpeningInschrijving()
    {
        $instellingGeopend = $this->getOrganisatieInstellingen(self::OPENING_INSCHRIJVING);
        if (time() > strtotime($instellingGeopend[self::OPENING_INSCHRIJVING])) {
            return true;
        }

        return false;
    }

    protected function wijzigTurnsterToegestaan()
    {
        $instellingGeopend = $this->getOrganisatieInstellingen(self::OPENING_INSCHRIJVING);
        $instellingGesloten = $this->getOrganisatieInstellingen(self::SLUITING_INSCHRIJVING_TURNSTERS);
        if ((time() > strtotime($instellingGeopend[self::OPENING_INSCHRIJVING]) &&
            time() < strtotime($instellingGesloten[self::SLUITING_INSCHRIJVING_TURNSTERS]))
        ) {
            return true;
        }
        return false;
    }

    protected function getJuryIndeling()
    {
        /** @var JuryIndeling[] $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:JuryIndeling')
            ->findBy(
                [],
                ['id' => 'DESC']
            );
        if ($result) {
            $juryIndeling = $result[0]->getAll();
            return $juryIndeling;
        } else {
            return false;
        }
    }

    protected function getTijdSchema()
    {
        /** @var TijdSchema[] $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:TijdSchema')
            ->findBy(
                [],
                ['id' => 'DESC']
            );
        if ($result) {
            $tijdSchema = $result[0]->getAll();
            return $tijdSchema;
        } else {
            return false;
        }
    }

    protected function verwijderenTurnsterToegestaan()
    {
        /** @var \DateTime[] $instellingGeopend */
        $instellingGeopend = $this->getOrganisatieInstellingen(self::OPENING_INSCHRIJVING);
        if ((time() > strtotime($instellingGeopend[self::OPENING_INSCHRIJVING]))
        ) {
            return true;
        }
        return false;
    }

    protected function wijzigJuryToegestaan()
    {
        $instellingGeopend = $this->getOrganisatieInstellingen(self::OPENING_INSCHRIJVING);
        $instellingGesloten = $this->getOrganisatieInstellingen(self::SLUITING_INSCHRIJVING_JURYLEDEN);
        if (time() > strtotime($instellingGeopend[self::OPENING_INSCHRIJVING]) &&
            time() < strtotime($instellingGesloten[self::SLUITING_INSCHRIJVING_JURYLEDEN])) {
            return true;
        } else {
            return false;
        }
    }

    protected function uploadenVloermuziekToegestaan()
    {
        $openingUploadenVloermuziek = $this->getOrganisatieInstellingen(self::OPENING_UPLOADEN_VLOERMUZIEK);
        $sluitingUploadenVloermuziek = $this->getOrganisatieInstellingen(self::SLUITING_UPLOADEN_VLOERMUZIEK);
        if ($openingUploadenVloermuziek[self::OPENING_UPLOADEN_VLOERMUZIEK] == self::EMPTY_RESULT) {
            return false;
        } elseif (time() > strtotime($openingUploadenVloermuziek[self::OPENING_UPLOADEN_VLOERMUZIEK]) && time() <
            strtotime($sluitingUploadenVloermuziek[self::SLUITING_UPLOADEN_VLOERMUZIEK])) {
            return true;
        } else {
            return false;
        }
    }

    protected function luisterenVloermuziekToegestaan()
    {
        $openingUploadenVloermuziek = $this->getOrganisatieInstellingen(self::OPENING_UPLOADEN_VLOERMUZIEK);
        if ($openingUploadenVloermuziek[self::OPENING_UPLOADEN_VLOERMUZIEK] == self::EMPTY_RESULT) {
            return false;
        } elseif (time() > strtotime($openingUploadenVloermuziek[self::OPENING_UPLOADEN_VLOERMUZIEK])) {
            return true;
        } else {
            return false;
        }
    }

    protected function factuurBekijkenToegestaan()
    {
        $factuurBekijkenToegestaan = $this->getOrganisatieInstellingen(self::FACTUUR_BEKIJKEN_TOEGESTAAN);
        if ($factuurBekijkenToegestaan[self::FACTUUR_BEKIJKEN_TOEGESTAAN] == self::EMPTY_RESULT) {
            return false;
        } elseif (time() > strtotime($factuurBekijkenToegestaan[self::FACTUUR_BEKIJKEN_TOEGESTAAN])) {
            return true;
        } else {
            return false;
        }
    }

    private function setSponsors()
    {
        $results = $this->getDoctrine()
            ->getRepository('AppBundle:Sponsor')
            ->findAll();
        foreach ($results as $result) {
            $this->sponsors[] = $result->getAll();
        }
        shuffle($this->sponsors);
    }

    private function setMenuItems($type)
    {
        $results = $this->getDoctrine()
            ->getRepository('AppBundle:' . $type . 'menuItem')
            ->findBy([], ['positie' => 'ASC']);
        foreach ($results as $result) {
            $this->menuItems[] = $result->getAll();
        }
    }

    private function setStatistieken()
    {
        $verenigingIds = [];
        /** @var User[] $results */
        $results = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();
        foreach ($results as $result) {
            if ($result->getRole() == 'ROLE_CONTACT') {
                if (!in_array($result->getVereniging()->getId(), $verenigingIds)) {
                    if ($turnstersAantal = $this->getDoctrine()
                        ->getRepository('AppBundle:Turnster')
                        ->getIngeschrevenTurnsters($result) > 0) {
                        $verenigingIds[] = $result->getVereniging()->getId();
                    }
                }
            }
        }
        $this->aantalVerenigingen = count($verenigingIds);
        $this->aantalTurnsters = $this->getDoctrine()
            ->getRepository('AppBundle:Turnster')
            ->getBezettePlekken();
        $this->aantalWachtlijst = $this->getDoctrine()
            ->getRepository('AppBundle:Turnster')
            ->getAantalWachtlijstPlekken();
        $this->aantalJury = $this->getDoctrine()
            ->getRepository('AppBundle:Jurylid')
            ->getTotaalAantalIngeschrevenJuryleden();
    }

    protected function checkIfPageExists($page)
    {
        if (in_array($page, ['Inschrijvingsinformatie'])) return true;
        $pageExists = false;
        foreach ($this->menuItems as $menuItem) {
            if ($menuItem['naam'] == $page) {
                $pageExists = true;
                break;
            }
            if ($menuItem['submenuItems']) {
                foreach ($menuItem['submenuItems'] as $submenuItem) {
                    if ($submenuItem['naam'] == $page) {
                        $pageExists = true;
                        break;
                    }
                }
            }
            if ($pageExists) {
                break;
            }
        }
        return $pageExists;
    }

    protected function maand($maandNummer)
    {
        switch ($maandNummer) {
            case '01':
                return 'Januari';
                break;
            case '02':
                return 'Februari';
                break;
            case '03':
                return 'Maart';
                break;
            case '04':
                return 'April';
                break;
            case '05':
                return 'Mei';
                break;
            case '06':
                return 'Juni';
                break;
            case '07':
                return 'Juli';
                break;
            case '08':
                return 'Augustus';
                break;
            case '09':
                return 'September';
                break;
            case '10':
                return 'Oktober';
                break;
            case '11':
                return 'November';
                break;
            case '12':
                return 'December';
                break;
        }
    }

    protected function generatePassword($length = 8)
    {
        $password = "";
        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
        $maxlength = strlen($possible);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }
        return $password;
    }

    protected function setBasicPageData($type = 'Hoofd')
    {
        $this->setMenuItems($type);
        $this->setSponsors();
        if ($type == 'Organisatie') {
            $this->setStatistieken();
        }
    }

    /**
     * Creates a token voor voorinschrijvingen
     * @return void
     */
    protected function createVoorinschrijvingToken($email, $tokenObject = null)
    {
        $token = sha1(mt_rand());
        if ($tokenObject === null) {
            $tokenObject = new Voorinschrijving();
        }
        $tokenObject->setToken($token);
        $tokenObject->setCreatedAt(new \DateTime('now'));
        $tokenObject->setTokenSentTo($email);

        $this->addToDB($tokenObject);

        $subject = 'Voorinschrijving HDC';
        $to = $email;
        $view = 'mails/voorinschrijving.txt.twig';
        $mailParameters = [
            'token' =>$token,
        ];
        $this->sendEmail($subject, $to, $view, $mailParameters);
    }

    /**
     * Creates a token usable in a form
     * @return string
     */
    protected function getToken()
    {
        $token = sha1(mt_rand());
        if (!isset($_SESSION['tokens'])) {
            $_SESSION['tokens'] = array($token => 1);
        } else {
            $_SESSION['tokens'][$token] = 1;
        }
        return $token;
    }

    /**
     * Check if a token is valid. Removes it from the valid tokens list
     * @param string $token The token
     * @return bool
     */
    protected function isTokenValid($token)
    {
        if (!empty($_SESSION['tokens'][$token])) {
            unset($_SESSION['tokens'][$token]);
            return true;
        }
        return false;
    }

    protected function sendEmail($subject, $to, $view, array $parameters = array(), $from = 'info@haagsedonarcup.nl')
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    $view,
                    array('parameters' => $parameters)
                ),
                'text/plain'
            );
        $this->get('mailer')->send($message);

        $sendMail = new SendMail();
        $sendMail->setDatum(new \DateTime())
            ->setVan($from)
            ->setAan($to)
            ->setOnderwerp($subject)
            ->setBericht($message->getBody());
        $this->addToDB($sendMail);
    }

    protected function addToDB($object, $detach = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($detach) {
            $em->detach($detach);
        }
        $em->persist($object);
        $em->flush();
    }

    protected function removeFromDB($object)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();
    }

    /**
     * @Route("/contactgegevens/edit/{fieldName}/{data}/", name="editGegevens", options={"expose"=true})
     * @Method("GET")
     */
    public function editGegevens($fieldName, $data)
    {
        if ($data == 'null') {
            $data = false;
        }
        /** @var User $userObject */
        $emptyConstraint = new EmptyConstraint();
        $userObject = $this->getUser();
        $returnData['data'] = '';
        $returnData['error'] = null;
        switch ($fieldName) {
            case 'voornaam':
                $returnData['data'] = $userObject->getVoornaam();
                $errors = $this->get('validator')->validate(
                    $data,
                    $emptyConstraint
                );
                if (count($errors) == 0) {
                    try {
                        $userObject->setVoornaam($data);
                        $this->addToDB($userObject);
                        $returnData['data'] = $userObject->getVoornaam();
                    } catch (\Exception $e) {
                        $returnData['error'] = $e->getMessage();
                    }
                } else {
                    foreach ($errors as $error) {
                        $returnData['error'] .= $error->getMessage() . ' ';
                    }
                }
                break;
            case 'achternaam':
                $returnData['data'] = $userObject->getAchternaam();
                $errors = $this->get('validator')->validate(
                    $data,
                    $emptyConstraint
                );
                if (count($errors) == 0) {
                    try {
                        $userObject->setAchternaam($data);
                        $this->addToDB($userObject);
                        $returnData['data'] = $userObject->getAchternaam();
                    } catch (\Exception $e) {
                        $returnData['error'] = $e->getMessage();
                    }
                } else {
                    foreach ($errors as $error) {
                        $returnData['error'] .= $error->getMessage() . ' ';
                    }
                }
                break;
            case 'email':
                $returnData['data'] = $userObject->getEmail();
                $errors = $this->get('validator')->validate(
                    $data,
                    $emptyConstraint
                );
                if (count($errors) == 0) {
                    $emailConstraint = new EmailConstraint();
                    $errors = $this->get('validator')->validate(
                        $data,
                        $emailConstraint
                    );
                    if (count($errors) == 0) {
                        try {
                            $userObject->setEmail($data);
                            $this->addToDB($userObject);
                            $returnData['data'] = $userObject->getEmail();
                        } catch (\Exception $e) {
                            $returnData['error'] = $e->getMessage();
                        }
                    } else {
                        foreach ($errors as $error) {
                            $returnData['error'] .= $error->getMessage() . ' ';
                        }
                    }
                } else {
                    foreach ($errors as $error) {
                        $returnData['error'] .= $error->getMessage() . ' ';
                    }
                }
                break;
            case 'telefoonnummer':
                $returnData['data'] = $userObject->getTelefoonnummer();
                $errors = $this->get('validator')->validate(
                    $data,
                    $emptyConstraint
                );
                if (count($errors) == 0) {
                    $re = '/^([0-9]+)$/';
                    if (preg_match($re, $data) && strlen($data) == 10) {
                        try {
                            $userObject->setTelefoonnummer($data);
                            $this->addToDB($userObject);
                            $returnData['data'] = $userObject->getTelefoonnummer();
                        } catch (\Exception $e) {
                            $returnData['error'] = $e->getMessage();
                        }
                    } else {
                        $returnData['error'] .= 'Het telefoonnummer moet uit precies 10 cijfers bestaan! ';
                    }
                } else {
                    foreach ($errors as $error) {
                        $returnData['error'] .= $error->getMessage() . ' ';
                    }
                }
                break;
            case 'verantwoordelijkheid':
                $returnData['data'] = $userObject->getVerantwoordelijkheid();
                try {
                    $userObject->setVerantwoordelijkheid($data);
                    $this->addToDB($userObject);
                    $returnData['data'] = $userObject->getVerantwoordelijkheid();
                } catch (\Exception $e) {
                    $returnData['error'] = $e->getMessage();
                }
                break;
            default:
                $returnData['error'] = 'An unknown error occurred, please contact webmaster@haagsedonarcup.nl';
        }
        return new JsonResponse($returnData);
    }

    private function factuurHeader(AlphaPDFController $pdf, $factuurNummer)
    {
        //BACKGROUND
        $pdf->Image('images/background4.png', 0, 0);    //BACKGROUND2: 0,45		BACKGROUND3: 17,77

        //LOGO
        $pdf->SetFillColor(127);
        $pdf->Rect(0, 0, 210, 35, 'F');
        $pdf->Image('images/HDCFactuurHeader.png');

        //FACTUUR, NUMMER EN DATUM
        $pdf->SetFont('Franklin', '', 16);
        $pdf->SetTextColor(255);
        $pdf->Text(5, 10, 'FACTUUR');
        $pdf->SetFontSize(10);
        $pdf->Text(6, 14, $factuurNummer);
        $datumFactuur = $this->getOrganisatieInstellingen(self::FACTUUR_BEKIJKEN_TOEGESTAAN);
        $pdf->Text(3, 32, 'Datum: ' . date('d-m-Y', strtotime($datumFactuur[self::FACTUUR_BEKIJKEN_TOEGESTAAN])));
        return $pdf;
    }

    //FOOTER
    private function factuurFooter(
        AlphaPDFController $pdf,
        $factuurNummer,
        $datumHBC,
        $locatieHBC,
        $rekeningNummer,
        $rekeningTNV
    ) {
        $pdf->SetX(3);
        $pdf->SetAlpha(0.6);
        $pdf->SetFont('Gotham', '', 8);
        $pdf->SetTextColor(0);

        //REKENINGNUMMER DETAILS
        $pdf->Text(3, 290, 'Haagse Donar Cup - ' . $datumHBC . ', ' . $locatieHBC);
        $pdf->Text(3, 294, $rekeningNummer . ' - T.n.v. ' . $rekeningTNV . ' o.v.v. ' . $factuurNummer);

        //LOGO DONAR
        $pdf->Image('images/logodonarPNG.png', 188, 268);

        //LOGO HDC
        $pdf->Image('images/logohbcPNG.png', 8, 268);

        //DONAR SITE
        $pdf->Text(180, 290, 'www.donargym.nl');

        //HBC SITE
        $pdf->Text(171, 294, 'www.haagsedonarcup.nl');
        return $pdf;
    }

    //ROUNDED RECTANGLE
    function RoundedRect($x, $y, $w, $h, $r, $style = '', $angle = '1234', AlphaPDFController $pdf)
    {
        $k = $pdf->k;
        $hp = $pdf->h;
        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' or $style == 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $pdf->_out(sprintf('%.2f %.2f m', ($x + $r) * $k, ($hp - $y) * $k));

        $xc = $x + $w - $r;
        $yc = $y + $r;
        $pdf->_out(sprintf('%.2f %.2f l', $xc * $k, ($hp - $y) * $k));
        if (strpos($angle, '2') === false) {
            $pdf->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - $y) * $k));
        } else {
            $pdf = $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc, $pdf);
        }

        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $pdf->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - $yc) * $k));
        if (strpos($angle, '3') === false) {
            $pdf->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - ($y + $h)) * $k));
        } else {
            $pdf = $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r, $pdf);
        }

        $xc = $x + $r;
        $yc = $y + $h - $r;
        $pdf->_out(sprintf('%.2f %.2f l', $xc * $k, ($hp - ($y + $h)) * $k));
        if (strpos($angle, '4') === false) {
            $pdf->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - ($y + $h)) * $k));
        } else {
            $pdf = $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc, $pdf);
        }

        $xc = $x + $r;
        $yc = $y + $r;
        $pdf->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - $yc) * $k));
        if (strpos($angle, '1') === false) {
            $pdf->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - $y) * $k));
            $pdf->_out(sprintf('%.2f %.2f l', ($x + $r) * $k, ($hp - $y) * $k));
        } else {
            $pdf = $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r, $pdf);
        }
        $pdf->_out($op);
        return $pdf;
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3, AlphaPDFController $pdf)
    {
        $h = $pdf->h;
        $pdf->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1 * $pdf->k, ($h - $y1) * $pdf->k,
            $x2 * $pdf->k, ($h - $y2) * $pdf->k, $x3 * $pdf->k, ($h - $y3) * $pdf->k));
        return $pdf;
    }

    /**
     * @Route("/contactpersoon/factuur/", name="pdfFactuur")
     * @Method("GET")
     */
    public function pdfFactuur($userId = null)
    {
        if ($this->factuurBekijkenToegestaan()) {
            if (!$this->getUser()) {
                return $this->redirectToRoute('getIndexPage');
            }
            if ($this->getUser()->getRole() == 'ROLE_ORGANISATIE' || $this->getUser()->getRole() == 'ROLE_CONTACT') {
                if ($this->getUser()->getRole() != 'ROLE_ORGANISATIE') {
                    $user = $this->getUser();
                } else {
                    $user = $this->getDoctrine()
                        ->getRepository('AppBundle:User')
                        ->findOneBy(['id' => $userId]);
                }
                $factuurNummer = $this->getFactuurNummer($user);
                $bedragPerTurnster = self::BEDRAG_PER_TURNSTER; //todo: bedrag per turnster toevoegen aan instellingen
                $juryBoeteBedrag = self::JURY_BOETE_BEDRAG; //todo: boete bedrag jury tekort toevoegen aan instellingen
                $datumHBC = self::DATUM_HBC; // todo: datum toernooi toevoegen aan instellingen
                $locatieHBC = self::LOCATIE_HBC; //todo: locatie toernooi toevoegen aan instellingen
                $rekeningNummer = self::REKENINGNUMMER; // todo: rekeningnummer toevoegen aan instellingen
                $rekeningTNV = self::REKENING_TNV; // todo: TNV toevoegen aan instellingen
                $jurylidPerAantalTurnsters = self::AANTAL_TURNSTERS_PER_JURY; //todo: toevoegen als instelling
                $juryledenAantal = $this->getDoctrine()
                    ->getRepository('AppBundle:Jurylid')
                    ->getIngeschrevenJuryleden($user);
                $turnstersAantal = $this->getDoctrine()
                    ->getRepository('AppBundle:Turnster')
                    ->getIngeschrevenTurnsters($user);
                $turnstersAfgemeldAantal = $this->getDoctrine()
                    ->getRepository('AppBundle:Turnster')
                    ->getAantalAfgemeldeTurnsters($user);

                $teLeverenJuryleden = ceil($turnstersAantal / $jurylidPerAantalTurnsters);
                if (($juryTekort = $teLeverenJuryleden - $juryledenAantal) < 0) {
                    $juryTekort = 0;
                }
                $teBetalenBedrag = ($turnstersAantal + $turnstersAfgemeldAantal) * $bedragPerTurnster + $juryTekort *
                    $juryBoeteBedrag;

                /** @var User $user */
                //START OF PDF
                $pdf = new AlphaPDFController();
                $pdf->SetMargins(0, 0);
                $pdf->AddFont('Gotham', '', 'Gotham-Light.php');
                $pdf->AddFont('Franklin', '', 'Frabk.php');
                $pdf->AddPage();

                $pdf = $this->factuurHeader($pdf, $factuurNummer);
                $pdf = $this->factuurFooter($pdf, $factuurNummer, $datumHBC, $locatieHBC, $rekeningNummer,
                    $rekeningTNV);

                //CONTACTPERSOON EN VERENIGING
                $pdf->SetFont('Franklin', '', 16);
                $pdf->SetTextColor(0);
                $pdf->SetFillColor(0);
				$pdf->SetAlpha(1.0);
                $pdf->Rect(5, 43, 0.5, 13, 'F');
                $pdf->Text(7, 48, $user->getVoornaam() . ' ' . $user->getAchternaam());
                $pdf->Text(7, 54, $user->getVereniging()->getNaam() . ' ' . $user->getVereniging()->getPlaats());

                //HR LINE
                $pdf->Rect(0, 63, 210, 0.3, 'F');

                //LINE BREAK
                $pdf->Ln(45);

                //FACTUURTABEL
                //EERSTE RIJ - HEADERS
                $pdf->Cell(20, 0);        //Blank space
                $pdf->SetFont('Gotham', '', 16);
                $pdf->Cell(97, 0, ' OMSCHRIJVING'); //De spatie voor OMSCHRIJVING hoort daar!
                $pdf->Cell(26, 0, 'AANTAL');
                $pdf->Cell(17, 0);        //Blank space
                $pdf->Cell(25, 0, 'BEDRAG');
                $pdf->Ln(8);
                //EURO-TEKENS
                $pdf->SetFont('Courier', '', 14);
                $pdf->Text(161, 89.9, EURO);
                $pdf->Text(161, 96.9, EURO);
                $pdf->Text(161, 103.9, EURO);
                $pdf->Text(161, 110.9, '');
                $pdf->SetFont('Gotham', '', 12);
                //TWEEDE RIJ - TURNSTERS
                $pdf->Cell(22, 0);        //Blank space
                $pdf->Cell(95, 0, 'Deelnemende turnsters');
                $pdf->Cell(26, 0, $turnstersAantal, 0, 0, 'C');
                $pdf->Cell(17, 0);        //Blank space
                $pdf->Cell(25, 0, ($turnstersAantal * $bedragPerTurnster), 0, 0, 'R');
                $pdf->Ln(7);
                //DERDE RIJ - AFGEMELDE TURNSTERS
                $pdf->Cell(22, 0);        //Blank space
                $pdf->Cell(95, 0, 'Afgemelde turnsters (na sluiting inschrijving)');
                $pdf->Cell(26, 0, $turnstersAfgemeldAantal, 0, 0, 'C');
                $pdf->Cell(17, 0);        //Blank space
                $pdf->Cell(25, 0, ($turnstersAfgemeldAantal * $bedragPerTurnster), 0, 0, 'R');
                $pdf->Ln(7);
                //VIERDE RIJ - JURYLEDEN TEKORT
                $pdf->Cell(22, 0);        //Blank space
                $pdf->Cell(95, 0, 'Tekort aan juryleden');
                $pdf->Cell(26, 0, $juryTekort, 0, 0, 'C');
                $pdf->Cell(17, 0);        //Blank space
                $pdf->Cell(25, 0, ($juryTekort * $juryBoeteBedrag), 0, 0, 'R');
                $pdf->Ln(7);
                //VIJFDE RIJ - ARRANGEMENT ZATERDAG
                $pdf->Cell(22, 0);        //Blank space
                $pdf->Cell(95, 0, '');
                $pdf->Cell(26, 0, '', 0, 0, 'C');
                $pdf->Cell(17, 0);        //Blank space
                $pdf->Cell(25, 0, '', 0, 0, 'R');
                $pdf->Ln(7);
                //TOTAALBEDRAG HR LINE
                $pdf->Rect(115, 116, 72, 0.2, 'F');
                $pdf->Ln(6);
                //ZESDE RIJ - TOTAALBEDRAG
                $pdf->SetAlpha(0.6);
                $pdf->SetFillColor(255, 255, 0);
                $pdf = $this->RoundedRect(115, 118.5, 72, 8, 2, 'F', 1234, $pdf);
                $pdf->SetAlpha(1);
                $pdf->SetFontSize(14);
                $pdf->Cell(22, 0);        //Blank space
                $pdf->Cell(95, 0);        //Blank space
                $pdf->Cell(26, 0, 'TOTAAL');
                $pdf->Cell(17, 0);        //Blank space
                $pdf->Cell(25, 0, $teBetalenBedrag, 0, 0, 'R');
                $pdf->Ln(7);
                //TOTAAL EURO-TEKEN
                $pdf->SetFont('Courier', '', 16);
                $pdf->Text(161, 123.9, EURO);
                $pdf->SetFont('Gotham', '', 12);

                //FILL COLOR BACK TO BLACK
                $pdf->SetFillColor(0);

                //HR LINE
                $pdf->Rect(0, 139, 210, 0.3, 'F');

                //LINE BREAK
                $pdf->Ln(16);

                //BETAALDETAILS
                $pdf->Cell(3, 35);
                $pdf->SetFontSize(12);
                $pdf->MultiCell(53, 5,
                    "Over te maken bedrag: \n Uiterste betaaldatum: \n \n Rekeningnummer: \n Ten name van: \n\n Factuurnummer:",
                    0, 'R');

                //EURO-TEKEN
                $pdf->SetFont('Courier', '', 13);
                $pdf->Text(57, 149.5, EURO);
                $pdf->SetFont('Gotham', '', 10);

                //BEDRAG
                $pdf->Text(61, 149.5, $teBetalenBedrag);

                //BETAALDATUM
                $uitersteBetaalDatum = $this->getOrganisatieInstellingen(self::UITERLIJKE_BETAALDATUM_FACTUUR);
                $pdf->Text(57, 154.5, date('d-m-Y', strtotime
                ($uitersteBetaalDatum[self::UITERLIJKE_BETAALDATUM_FACTUUR])));

                //REKENINGNUMMER
                $pdf->Text(57, 164.5, $rekeningNummer);

                //TNV
                $pdf->Text(57, 169.5, $rekeningTNV);

                //FACTUURNUMMER
                $pdf->Text(57, 179.5, $factuurNummer);

                //BETAALINSTRUCTIES
                //$pdf->SetFillColor(0,148,255); BLAUWE ACHTERGROND
                //ANDERE OPTIES: GELE ACHTERGROND

                $pdf->SetFillColor(0);
                $pdf->SetAlpha(0.5);
                $pdf = $this->RoundedRect(105.5, 144, 100, 38, 2, 'F', 1234, $pdf);
                $pdf->SetAlpha(1);

                $pdf->SetFontSize(14);
                $pdf->SetTextColor(255, 255, 0);
                $pdf->Text(130.5, 151, 'BETAALINSTRUCTIES');

                $pdf->SetTextColor(255);
                $pdf->SetFontSize(12);
                $pdf->Text(120, 158, 'Wij verzoeken u vriendelijk om het');
                $pdf->Text(116, 163, 'verschuldigde bedrag voor de uiterste');
                $pdf->Text(118, 168, 'betaaldatum over te maken naar het');
                $pdf->Text(109, 173, 'genoemde rekeningnummer. Vermeld bij het');
                $pdf->Text(116, 178, 'opmerkingenveld uw factuurnummer.');

                //DEFINITIEF NA BETALING
                $pdf->SetDrawColor(0);
                $pdf->SetTextColor(0);
                $pdf->Rect(4, 199, 202, 7, 'D');
                $pdf->Text(31, 204, 'Let op! Uw inschrijving is pas definitief zodra uw betaling is ontvangen.');

                //CONTACT BIJ PROBLEMEN
                $pdf->SetAlpha(0.6);
                $pdf->SetFontSize(8);
                $pdf->Text(34, 209,
                    'Mochten er zich problemen voordoen, neemt u dan alstublieft contact op via info@haagsedonarcup.nl');
                return new Response($pdf->Output(), 200, array(
                    'Content-Type' => 'application/pdf'
                ));
            } else {
                return $this->redirectToRoute('getIndexPage');
            }
        } else {
            return $this->redirectToRoute('getIndexPage');
        }
    }

    /**
     * @Route("/updateScores/{wedstrijdnummer}/", name="updateScores")
     * @Method({"GET"})
     */
    public function updateScores(Request $request, $wedstrijdnummer)
    {
        if ($request->query->get('key') && $request->query->get('key') === $this->getParameter('update_scores_string')) {
            $toestellen = ['sprong', 'brug', 'balk', 'vloer'];
            if ($request->query->get('toestel') && in_array(strtolower($request->query->get('toestel')), $toestellen)) {
                /** @var Scores $score */
                $score = $this->getDoctrine()->getRepository('AppBundle:Scores')
                    ->findOneBy(['wedstrijdnummer' => $wedstrijdnummer]);
                if ($score) {
                    switch (strtolower($request->query->get('toestel'))) {
                        case 'sprong':
                            if ($request->query->get('dSprong1') !== null && $request->query->get('eSprong1') !== null &&
                                $request->query->get('nSprong1') !== null && $request->query->get('dSprong2') !== null &&
                                $request->query->get('eSprong2') !== null && $request->query->get('nSprong2') !== null) {
                                try {
                                    $score->setDSprong1($request->query->get('dSprong1'));
                                    $score->setESprong1($request->query->get('eSprong1'));
                                    $score->setNSprong1($request->query->get('nSprong1'));
                                    $score->setDSprong2($request->query->get('dSprong2'));
                                    $score->setESprong2($request->query->get('eSprong2'));
                                    $score->setNSprong2($request->query->get('nSprong2'));
                                    $score->setUpdatedSprong(new \DateTime('now'));
                                    $this->addToDB($score);
                                } catch (\Exception $e) {
                                    return new Response($e->getMessage(), 500);
                                }
                                return new Response('ok', 200);
                            } else {
                                return new Response('Niet alle verplichte gegevens zijn opgegeven', 500);
                            }
                            break;
                        case 'brug':
                            if ($request->query->get('dBrug') !== null && $request->query->get('eBrug') !== null &&
                                $request->query->get('nBrug') !== null) {
                                try {
                                    $score->setDBrug($request->query->get('dBrug'));
                                    $score->setEBrug($request->query->get('eBrug'));
                                    $score->setNBrug($request->query->get('nBrug'));
                                    $score->setUpdatedBrug(new \DateTime('now'));
                                    $this->addToDB($score);
                                } catch (\Exception $e) {
                                    return new Response($e->getMessage(), 500);
                                }
                                return new Response('ok', 200);
                            } else {
                                return new Response('Niet alle verplichte gegevens zijn opgegeven', 500);
                            }
                            break;
                        case 'balk':
                            if ($request->query->get('dBalk') !== null && $request->query->get('eBalk') !== null &&
                                $request->query->get('nBalk') !== null) {
                                try {
                                    $score->setDBalk($request->query->get('dBalk'));
                                    $score->setEBalk($request->query->get('eBalk'));
                                    $score->setNBalk($request->query->get('nBalk'));
                                    $score->setUpdatedBalk(new \DateTime('now'));
                                    $this->addToDB($score);
                                } catch (\Exception $e) {
                                    return new Response($e->getMessage(), 500);
                                }
                                return new Response('ok', 200);
                            } else {
                                return new Response('Niet alle verplichte gegevens zijn opgegeven', 500);
                            }
                            break;
                        case 'vloer':
                            if ($request->query->get('dVloer') !== null && $request->query->get('eVloer') !== null &&
                                $request->query->get('nVloer') !== null) {
                                try {
                                    $score->setDVloer($request->query->get('dVloer'));
                                    $score->setEVloer($request->query->get('eVloer'));
                                    $score->setNVloer($request->query->get('nVloer'));
                                    $score->setUpdatedVloer(new \DateTime('now'));
                                    $this->addToDB($score);
                                } catch (\Exception $e) {
                                    return new Response($e->getMessage(), 500);
                                }
                                return new Response('ok', 200);
                            } else {
                                return new Response('Niet alle verplichte gegevens zijn opgegeven', 500);
                            }
                            break;
                    }
                } else {
                    return new Response('Geen geldig wedstrijdnummer', 500);
                }
            } else {
                return new Response('Invalid toestel', 500);
            }
            return new Response('Internal server error', 500);
        }
        return new Response('Invalid key!', 403);
    }

    /**
     * @Route("/publiceerUitslag/{categorie}/{niveau}/", name="publiceerUitslag")
     * @Method({"GET"})
     */
    public function publiceerUitslag(Request $request, $categorie, $niveau)
    {
        if ($request->query->get('key') && $request->query->get('key') === $this->getParameter('update_scores_string')) {
            /** @var ToegestaneNiveaus $result */
            $result = $this->getDoctrine()->getRepository('AppBundle:ToegestaneNiveaus')
                ->findOneBy([
                    'categorie' => $categorie,
                    'niveau' => $niveau,
                ]);
            if ($result) {
                try {
                    $result->setUitslagGepubliceerd(true);
                    $this->addToDB($result);
                    return new Response('ok', 200);
                } catch (\Exception $e) {
                    return new Response($e->getMessage(), 500);
                }
            } else {
                return new Response('Combinatie van niveau/categorie niet gevonden!', 500);
            }
        }
        return new Response('Invalid key!', 403);
    }

    /**
     * @Route("/getScore/{wedstrijdnummer}/{toestel}/", name="getScore")
     * @Method({"GET"})
     */
    public function getScore(Request $request, $wedstrijdnummer, $toestel)
    {
        if ($request->query->get('key') && $request->query->get('key') === $this->getParameter('update_scores_string')) {
            $toestellen = ['sprong', 'brug', 'balk', 'vloer'];
            if (in_array(strtolower($toestel), $toestellen)) {
                /** @var Scores $score */
                $score = $this->getDoctrine()->getRepository('AppBundle:Scores')
                    ->findOneBy(['wedstrijdnummer' => $wedstrijdnummer]);
                if ($score) {
                    switch (strtolower($toestel)) {
                        case 'sprong':
			    $scoreArray = [
				'dSprong1' => $score->getDSprong1(),
                                'eSprong1' => $score->getESprong1(),
                                'nSprong1' => $score->getNSprong1(),
                                'dSprong2' => $score->getDSprong2(),
                                'eSprong2' => $score->getESprong2(),
                                'nSprong2' => $score->getNSprong2(),
			    ];
			    $responseData = json_encode($scoreArray);
                            return new Response($responseData, 200);
                            break;
                        case 'brug':
                            $scoreArray = [
				'dBrug' => $score->getDBrug(),
                                'eBrug' => $score->getEBrug(),
                                'nBrug' => $score->getNBrug(),
			    ];
			    $responseData = json_encode($scoreArray);
                            return new Response($responseData, 200);
                            break;
                        case 'balk':
                            $scoreArray = [
				'dBalk' => $score->getDBalk(),
                                'eBalk' => $score->getEBalk(),
                                'nBalk' => $score->getNBalk(),
			    ];
			    $responseData = json_encode($scoreArray);
                            return new Response($responseData, 200);
                            break;
                        case 'vloer':
                            $scoreArray = [
				'dVloer' => $score->getDVloer(),
                                'eVloer' => $score->getEVloer(),
                                'nVloer' => $score->getNVloer(),
			    ];
			    $responseData = json_encode($scoreArray);
                            return new Response($responseData, 200);
                            break;
                    }
                } else {
                    return new Response('Geen geldig wedstrijdnummer', 500);
                }
            } else {
                return new Response('Invalid toestel', 500);
            }
            return new Response('Internal server error', 500);
        }
        return new Response('Invalid key!', 403);
    }

    protected function getRanking($scores, $order = '')
    {
        $toestellen = ['Sprong', 'Brug', 'Balk', 'Vloer', ''];
        foreach ($toestellen as $toestel) {
            usort($scores, function ($a, $b) use ($toestel) {
                $epsilon = 0.00001;
                if (abs($a['totaal' . $toestel] - $b['totaal' . $toestel]) < $epsilon) {
                    return 0;
                }
                return ($a['totaal' . $toestel] > $b['totaal' . $toestel]) ? -1 : 1;
            });
            $epsilon = 0.00001;
            for ($i = 1; $i <= count($scores); $i++) {
                if ($i == 1) {
                    $scores[($i - 1)]['rank' . $toestel] = $i;
                } elseif (abs($scores[($i - 1)]['totaal' . $toestel] - $scores[($i - 2)]['totaal' . $toestel]) < $epsilon) {
                    $scores[($i - 1)]['rank' . $toestel] = $scores[($i - 2)]['rank' . $toestel];
                } else {
                    $scores[($i - 1)]['rank' . $toestel] = $i;
                }
            }
        }
        usort($scores, function ($a, $b) use ($order) {
            if ($a['totaal' . $order] == $b['totaal' . $order]) {
                return 0;
            }
            return ($a['totaal' . $order] > $b['totaal' . $order]) ? -1 : 1;
        });
        return $scores;
    }
}
