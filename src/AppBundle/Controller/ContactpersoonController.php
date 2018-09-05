<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Betaling;
use AppBundle\Entity\FileUpload;
use AppBundle\Entity\FotoUpload;
use AppBundle\Entity\Jurylid;
use AppBundle\Entity\Nieuwsbericht;
use AppBundle\Entity\Scores;
use AppBundle\Entity\ScoresRepository;
use AppBundle\Entity\Sponsor;
use AppBundle\Entity\Turnster;
use AppBundle\Entity\TurnsterRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\Vloermuziek;
use AppBundle\Form\Type\EditSponsorType;
use AppBundle\Form\Type\NieuwsberichtType;
use AppBundle\Form\Type\OrganisatieType;
use AppBundle\Form\Type\SponsorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Content;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception;
use AppBundle\Controller\BaseController;
use AppBundle\Form\Type\ContentType;
use Symfony\Component\Validator\Constraints\DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * @Security("has_role('ROLE_CONTACT')")
 */
class ContactpersoonController extends BaseController
{
    /**
     * @Route("/contactpersoon/", name="getContactpersoonIndexPage")
     * @Method("GET")
     */
    public function getIndexPageAction()
    {
        $indelingen = $this->getWedstrijdindelingen();
        $uploadenVloermuziekToegestaan = $this->uploadenVloermuziekToegestaan();
        $wijzigenTurnsterToegestaan = $this->wijzigTurnsterToegestaan();
        $verwijderenTurnsterToegestaan = $this->verwijderenTurnsterToegestaan();
        $wijzigJuryToegestaan = $this->wijzigJuryToegestaan();
        $verwijderJuryToegestaan = $this->wijzigJuryToegestaan();
        $factuurBekijkenToegestaan = $this->factuurBekijkenToegestaan();
        $this->updateGereserveerdePlekken();
        $this->setBasicPageData();
        /** @var User $user */
        $user = $this->getUser();
        $contactgevens = [
            'vereniging' => $user->getVereniging()->getNaam() . ', ' . $user->getVereniging()->getPlaats(),
            'gebruikersnaam' => $user->getUsername(),
            'voornaam' => $user->getVoornaam(),
            'achternaam' => $user->getAchternaam(),
            'email' => $user->getEmail(),
            'telNr' => $user->getTelefoonnummer(),
        ];
        $turnsters = [];
        $wachtlijst = [];
        $afgemeld = [];
        /** @var Turnster[] $turnsterObjecten */
        $turnsterObjecten = $user->getTurnster();
        foreach ($turnsterObjecten as $turnsterObject) {
            if ($turnsterObject->getVloermuziek()) {
                $vloermuziek = true;
                $locatie = $turnsterObject->getVloermuziek()->getWebPath();
            } else {
                $vloermuziek = false;
                $locatie = '';
            }
            if ($turnsterObject->getAfgemeld()) {
                $afgemeld[] = [
                    'id' => $turnsterObject->getId(),
                    'voornaam' => $turnsterObject->getVoornaam(),
                    'achternaam' => $turnsterObject->getAchternaam(),
                    'geboorteJaar' => $turnsterObject->getGeboortejaar(),
                    'categorie' => $this->getCategorie($turnsterObject->getGeboortejaar()),
                    'niveau' => $turnsterObject->getNiveau(),
                    'opmerking' => $turnsterObject->getOpmerking(),
                ];
            } elseif ($turnsterObject->getWachtlijst()) {
                $wachtlijst[] = [
                    'id' => $turnsterObject->getId(),
                    'voornaam' => $turnsterObject->getVoornaam(),
                    'achternaam' => $turnsterObject->getAchternaam(),
                    'geboorteJaar' => $turnsterObject->getGeboortejaar(),
                    'categorie' => $this->getCategorie($turnsterObject->getGeboortejaar()),
                    'niveau' => $turnsterObject->getNiveau(),
                    'opmerking' => $turnsterObject->getOpmerking(),
                ];
            } else {
                $turnsters[] = [
                    'id' => $turnsterObject->getId(),
                    'voornaam' => $turnsterObject->getVoornaam(),
                    'achternaam' => $turnsterObject->getAchternaam(),
                    'geboorteJaar' => $turnsterObject->getGeboortejaar(),
                    'categorie' => $this->getCategorie($turnsterObject->getGeboortejaar()),
                    'niveau' => $turnsterObject->getNiveau(),
                    'wedstrijdnummer' => $turnsterObject->getScores()->getWedstrijdnummer(),
                    'vloermuziek' => $vloermuziek,
                    'opmerking' => $turnsterObject->getOpmerking(),
                    'keuze' => $this->isKeuzeOefenstof($turnsterObject->getGeboortejaar()),
                    'wedstrijddag' => $turnsterObject->getScores()->getWedstrijddag(),
                    'baan' => $turnsterObject->getScores()->getBaan(),
                    'wedstrijdronde' => $turnsterObject->getScores()->getWedstrijdronde(),
                    'groep' => $turnsterObject->getScores()->getGroep(),
                    'vloermuziekLocatie' => $locatie,
                ];
            }
        }
        $juryleden = [];
        /** @var Jurylid[] $juryObjecten */
        $juryObjecten = $user->getJurylid();
        foreach ($juryObjecten as $juryObject) {
            $juryleden[] = [
                'id' => $juryObject->getId(),
                'voornaam' => $juryObject->getVoornaam(),
                'achternaam' => $juryObject->getAchternaam(),
                'opmerking' => $juryObject->getOpmerking(),
                'brevet' => $juryObject->getBrevet(),
                'dag' => $this->getBeschikbareDag($juryObject),
            ];
        }
        $teLeverenJuryleden = ceil(count($turnsters)/10);
        if (($juryBoete = $teLeverenJuryleden - count($juryleden)) < 0) {
            $juryBoete = 0;
        }
        $teBetalenBedrag = (count($turnsters) + count($afgemeld)) * 15 + $juryBoete * 35;
        /** @var Betaling[] $betalingen */
        $betalingen = $user->getBetaling();
        $betaaldBedrag = 0;
        if (count($betalingen) == 0) {
            $factuurId = 'factuur';
        } else {
            foreach ($betalingen as $betaling) {
                $betaaldBedrag += $betaling->getBedrag();
            } if ($betaaldBedrag < $teBetalenBedrag) {
                $factuurId = 'factuur_deel';
            } else {
                $factuurId = 'factuur_voldaan';
            }
        }
        return $this->render('contactpersoon/contactpersoonIndex.html.twig', array(
            'menuItems' => $this->menuItems,
            'sponsors' => $this->sponsors,
            'contactgegevens' => $contactgevens,
            'turnsters' => $turnsters,
            'wachtlijstTurnsters' => $wachtlijst,
            'afgemeldTurnsters' => $afgemeld,
            'juryleden' => $juryleden,
            'wijzigenTurnsterToegestaan' => $wijzigenTurnsterToegestaan,
            'verwijderenTurnsterToegestaan' => $verwijderenTurnsterToegestaan,
            'wijzigJuryToegestaan' => $wijzigJuryToegestaan,
            'verwijderJuryToegestaan' => $verwijderJuryToegestaan,
            'uploadenVloermuziekToegestaan' => $uploadenVloermuziekToegestaan,
            'factuurBekijkenToegestaan' => $factuurBekijkenToegestaan,
            'factuurId' => $factuurId,
            'banen' => $indelingen['banen'],
            'dagen' => $indelingen['dagen'],
            'wedstrijdrondes' => $indelingen['wedstrijdrondes'],
            'categorieNiveau' => $indelingen['categorieNiveau'],
        ));
    }

    /**
     * @Route("/contactpersoon/uitslagen/", name="contactpersoonUitslagen")
     * @Method({"GET"})
     */
    public function contactpersoonUitslagen()
    {
        /** @var TurnsterRepository $repo */
        $repo = $this->getDoctrine()->getRepository('AppBundle:Turnster');
        $catNivs = $repo->getDistinctCatNiv($this->getUser()->getId());
        $pdf = new UitslagenPdfController('L', 'mm', 'A4');
        foreach ($catNivs as $catNiv) {
            $check = $this->getDoctrine()->getRepository('AppBundle:ToegestaneNiveaus')
                ->findOneBy([
                    'categorie' => $catNiv['categorie'],
                    'niveau' => $catNiv['niveau'],
                    'uitslagGepubliceerd' => 1,
                ]);
            if ($check) {
                /** @var Turnster[] $results */
                $results = $this->getDoctrine()->getRepository("AppBundle:Turnster")
                    ->getIngeschrevenTurnstersCatNiveau($catNiv['categorie'], $catNiv['niveau']);
                $turnsters = [];
                foreach ($results as $result) {
                    $turnsters[] = $result->getUitslagenLijst();
                }
                $turnsters = $this->getRanking($turnsters);
                $pdf->setCategorie($catNiv['categorie']);
                $pdf->setNiveau($catNiv['niveau']);
                $pdf->SetLeftMargin(7);
                $pdf->AliasNbPages();
                $pdf->AddPage();
                $pdf->Table($turnsters, $this->getUser()->getId());
            }
        }
        return new Response($pdf->Output(
            'Uitslagen ' . $this->getUser()->getVereniging()->getNaam() . ' ' . $this->getUser()->getVereniging()
                ->getPlaats() . ' HDC ' . self::DATUM_HBC . ".pdf", "I"
        ), 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * @Route("/contactpersoon/addTurnster/", name="addTurnster")
     * @Method({"GET", "POST"})
     */
    public function addTurnster(Request $request)
    {
        if ($this->wijzigTurnsterToegestaan()) {
            $this->setBasicPageData();
            $turnster = [
                'voornaam' => '',
                'achternaam' => '',
                'geboortejaar' => '',
                'niveau' => '',
                'opmerking' => '',
            ];
            $classNames = [
                'voornaam' => 'text',
                'achternaam' => 'text',
                'geboortejaar' => 'turnster_niveau',
                'niveau' => 'turnster_niveau',
                'opmerking' => 'text',
            ];
            $geboorteJaren = $this->getGeboorteJaren();
            $vrijePlekken = $this->getVrijePlekken();
            $csrfToken = $this->getToken();
            if ($request->getMethod() == 'POST') {
                $turnster = [
                    'voornaam' => $request->request->get('voornaam'),
                    'achternaam' => $request->request->get('achternaam'),
                    'geboortejaar' => $request->request->get('geboorteJaar'),
                    'niveau' => $request->request->get('niveau'),
                    'opmerking' => $request->request->get('opmerking'),
                ];
                $postedToken = $request->request->get('csrfToken');
                if (!empty($postedToken)) {
                    if ($this->isTokenValid($postedToken)) {
                        $validationTurnster = [
                            'voornaam' => false,
                            'achternaam' => false,
                            'geboorteJaar' => false,
                            'niveau' => false,
                            'opmerking' => true,
                        ];

                        $classNames['opmerking'] = 'succesIngevuld';

                        if (strlen($request->request->get('voornaam')) > 1) {
                            $validationTurnster['voornaam'] = true;
                            $classNames['voornaam'] = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige voornaam ingevoerd'
                            );
                            $classNames['voornaam'] = 'error';
                        }

                        if (strlen($request->request->get('achternaam')) > 1) {
                            $validationTurnster['achternaam'] = true;
                            $classNames['achternaam'] = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige achternaam ingevoerd'
                            );
                            $classNames['achternaam'] = 'error';
                        }
                        if ($request->request->get('geboorteJaar')) {
                            $validationTurnster['geboorteJaar'] = true;
                            $classNames['geboortejaar'] = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geboortejaar ingevoerd'
                            );
                            $classNames['geboortejaar'] = 'error';
                        }

                        if ($request->request->get('niveau')) {
                            $validationTurnster['niveau'] = true;
                            $classNames['niveau'] = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen niveau ingevoerd'
                            );
                            $classNames['niveau'] = 'error';
                        }
                        if (!(in_array(false, $validationTurnster))) {
                            $turnster = new Turnster();
                            $scores = new Scores();
                            if ($this->getVrijePlekken() > 0) {
                                $turnster->setWachtlijst(false);
                            } else {
                                $turnster->setWachtlijst(true);
                            }
                            $turnster->setCreationDate(new \DateTime('now'));
                            $turnster->setExpirationDate(null);
                            $turnster->setScores($scores);
                            $turnster->setUser($this->getUser());
                            $turnster->setIngevuld(true);
                            $turnster->setVoornaam(trim($request->request->get('voornaam')));
                            $turnster->setAchternaam(trim($request->request->get('achternaam')));
                            $turnster->setGeboortejaar($request->request->get('geboorteJaar'));
                            $turnster->setCategorie($this->getCategorie($request->request->get('geboorteJaar')));
                            $turnster->setNiveau($request->request->get('niveau'));
                            $turnster->setOpmerking($request->request->get('opmerking'));
                            $this->getUser()->addTurnster($turnster);
                            $this->addToDB($this->getUser());
                            $this->addFlash(
                                'success',
                                'Gegevens succesvol toegevoegd!'
                            );
                            return $this->redirectToRoute('getContactpersoonIndexPage');
                        }
                    }
                }
            }
            return $this->render('contactpersoon/addTurnster.html.twig', array(
                'menuItems' => $this->menuItems,
                'sponsors' => $this->sponsors,
                'vrijePlekken' => $vrijePlekken,
                'turnster' => $turnster,
                'geboorteJaren' => $geboorteJaren,
                'classNames' => $classNames,
                'csrfToken' => $csrfToken,
            ));
        } else {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/editTurnster/{turnsterId}/", name="editTurnster")
     * @Method({"GET", "POST"})
     */
    public function editTurnster(Request $request, $turnsterId)
    {
        if ($this->wijzigTurnsterToegestaan()) {
            $this->setBasicPageData();
            /** @var Turnster $result */
            $result = $this->getDoctrine()->getRepository('AppBundle:Turnster')
                ->findOneBy(['id' => $turnsterId]);
            if (!$result) {
                $this->addFlash(
                    'error',
                    'Turnster niet gevonden'
                );
                return $this->redirectToRoute('getContactpersoonIndexPage');
            } elseif ($result->getUser() != $this->getUser()) {
                $this->addFlash(
                    'error',
                    'Not authorized!'
                );
                return $this->redirectToRoute('getContactpersoonIndexPage');
            } else {
                $turnster = [
                    'voornaam' => $result->getVoornaam(),
                    'achternaam' => $result->getAchternaam(),
                    'geboortejaar' => $result->getGeboortejaar(),
                    'niveau' => $result->getNiveau(),
                    'opmerking' => $result->getOpmerking(),
                ];
                $classNames = [
                    'voornaam' => 'text',
                    'achternaam' => 'text',
                    'geboortejaar' => 'turnster_niveau',
                    'niveau' => 'turnster_niveau',
                    'opmerking' => 'text',
                ];
                $geboorteJaren = $this->getGeboorteJaren();
                $csrfToken = $this->getToken();
                if ($request->getMethod() == 'POST') {
                    $turnster = [
                        'voornaam' => $request->request->get('voornaam'),
                        'achternaam' => $request->request->get('achternaam'),
                        'geboortejaar' => $request->request->get('geboorteJaar'),
                        'niveau' => $request->request->get('niveau'),
                        'opmerking' => $request->request->get('opmerking'),
                    ];
                    $postedToken = $request->request->get('csrfToken');
                    if (!empty($postedToken)) {
                        if ($this->isTokenValid($postedToken)) {
                            $validationTurnster = [
                                'voornaam' => false,
                                'achternaam' => false,
                                'geboorteJaar' => false,
                                'niveau' => false,
                                'opmerking' => true,
                            ];

                            $classNames['opmerking'] = 'succesIngevuld';

                            if (strlen($request->request->get('voornaam')) > 1) {
                                $validationTurnster['voornaam'] = true;
                                $classNames['voornaam'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige voornaam ingevoerd'
                                );
                                $classNames['voornaam'] = 'error';
                            }

                            if (strlen($request->request->get('achternaam')) > 1) {
                                $validationTurnster['achternaam'] = true;
                                $classNames['achternaam'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige achternaam ingevoerd'
                                );
                                $classNames['achternaam'] = 'error';
                            }
                            if ($request->request->get('geboorteJaar')) {
                                $validationTurnster['geboorteJaar'] = true;
                                $classNames['geboortejaar'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geboortejaar ingevoerd'
                                );
                                $classNames['geboortejaar'] = 'error';
                            }

                            if ($request->request->get('niveau')) {
                                $validationTurnster['niveau'] = true;
                                $classNames['niveau'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen niveau ingevoerd'
                                );
                                $classNames['niveau'] = 'error';
                            }
                            if (!(in_array(false, $validationTurnster))) {
                                $turnster = $result;
                                $turnster->setVoornaam(trim($request->request->get('voornaam')));
                                $turnster->setAchternaam(trim($request->request->get('achternaam')));
                                $turnster->setGeboortejaar($request->request->get('geboorteJaar'));
                                $turnster->setCategorie($this->getCategorie($request->request->get('geboorteJaar')));
                                $turnster->setNiveau($request->request->get('niveau'));
                                $turnster->setOpmerking($request->request->get('opmerking'));
                                $this->addToDB($turnster);
                                $this->addFlash(
                                    'success',
                                    'Gegevens succesvol gewijzigd!'
                                );
                                return $this->redirectToRoute('getContactpersoonIndexPage');
                            }
                        }
                    }
                }
                return $this->render('contactpersoon/editTurnster.html.twig', array(
                    'menuItems' => $this->menuItems,
                    'sponsors' => $this->sponsors,
                    'turnster' => $turnster,
                    'geboorteJaren' => $geboorteJaren,
                    'classNames' => $classNames,
                    'csrfToken' => $csrfToken,
                ));
            }
        } else {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/removeTurnster/", name="removeTurnster")
     * @Method({"POST"})
     */
    public function removeTurnster(Request $request)
    {
        /** @var Turnster $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->findOneBy(['id' => $request->request->get('turnsterId')]);
        if (!$result) {
            $this->addFlash(
                'error',
                'Turnster niet gevonden'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
        if ($result->getUser() != $this->getUser()) {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        } else {
            if ($this->wijzigTurnsterToegestaan() || $result->getWachtlijst()) {
                $this->removeFromDB($result);
            } else {
                $result->setAfgemeld(true);
                $this->addToDB($result);
            }
            $this->addFlash(
                'success',
                'Turnster succesvol afgemeld!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/addJury/", name="addJury")
     * @Method({"GET", "POST"})
     */
    public function addJury(Request $request)
    {
        if ($this->wijzigJuryToegestaan()) {
            $this->setBasicPageData();
            $jury = [
                'voornaam' => '',
                'achternaam' => '',
                'email' => '',
                'brevet' => '',
                'opmerking' => '',
                'dag' => '',
            ];
            $classNames = [
                'voornaam' => 'text',
                'achternaam' => 'text',
                'email' => 'text',
                'brevet' => 'turnster_niveau',
                'opmerking' => 'text',
                'dag' => 'turnster_niveau',
            ];
            $csrfToken = $this->getToken();
            if ($request->getMethod() == 'POST') {
                $jury = [
                    'voornaam' => $request->request->get('voornaam'),
                    'achternaam' => $request->request->get('achternaam'),
                    'email' => $request->request->get('email'),
                    'brevet' => $request->request->get('brevet'),
                    'dag' => $request->request->get('dag'),
                    'opmerking' => $request->request->get('opmerking'),
                ];
                $postedToken = $request->request->get('csrfToken');
                if (!empty($postedToken)) {
                    if ($this->isTokenValid($postedToken)) {
                        $validationJury = [
                            'voornaam' => false,
                            'achternaam' => false,
                            'email' => false,
                            'brevet' => false,
                            'dag' => false,
                            'opmerking' => true,
                        ];

                        $classNames['opmerking'] = 'succesIngevuld';

                        if (strlen($request->request->get('voornaam')) > 1) {
                            $validationJury['voornaam'] = true;
                            $classNames['voornaam'] = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige voornaam ingevoerd'
                            );
                            $classNames['voornaam'] = 'error';
                        }

                        if (strlen($request->request->get('achternaam')) > 1) {
                            $validationJury['achternaam'] = true;
                            $classNames['achternaam'] = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige achternaam ingevoerd'
                            );
                            $classNames['achternaam'] = 'error';
                        }

                        if (strlen($request->request->get('email')) > 1) {
                            $emailConstraint = new EmailConstraint();
                            $errors = $this->get('validator')->validate(
                                $request->request->get('email'),
                                $emailConstraint
                            );
                            if (count($errors) == 0) {
                                $validationJury['email'] = true;
                                $classNames['email'] = 'succesIngevuld';
                            } else {
                                foreach ($errors as $error) {
                                    $this->addFlash(
                                        'error',
                                        $error->getMessage()
                                    );
                                }
                                $classNames['email'] = 'error';
                            }
                        } else {
                            $this->addFlash(
                                'error',
                                'geen email ingevoerd'
                            );
                            $classNames['email'] = 'error';
                        }

                        if ($request->request->get('brevet')) {
                            $validationJury['brevet'] = true;
                            $classNames['brevet'] = 'brevet';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen brevet ingevoerd'
                            );
                            $classNames['brevet'] = 'error';
                        }

                        if ($request->request->get('dag')) {
                            $validationJury['dag'] = true;
                            $classNames['dag'] = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen dag ingevoerd'
                            );
                            $classNames['dag'] = 'error';
                        }
                        if (!(in_array(false, $validationJury))) {
                            $jurylid = new Jurylid();
                            $jurylid->setVoornaam(trim($request->request->get('voornaam')));
                            $jurylid->setAchternaam(trim($request->request->get('achternaam')));
                            $jurylid->setEmail($request->request->get('email'));
                            $jurylid->setBrevet($request->request->get('brevet'));
                            $jurylid->setOpmerking($request->request->get('opmerking'));
                            $this->setJurylidBeschikbareDagenFromPostData($request->request->get('dag'), $jurylid);
                            $jurylid->setUser($this->getUser());
                            $this->getUser()->addJurylid($jurylid);
                            $this->addToDB($this->getUser());
                            $this->addFlash(
                                'success',
                                'Jurylid succesvol toegevoegd!'
                            );

                            /** @var User $user */
                            $user = $this->getUser();
                            $subject    = 'Aanmelding Haagse Donar Cup';
                            $to         = $jurylid->getEmail();
                            $view       = 'mails/inschrijven_jurylid.html.twig';
                            $parameters = [
                                'voornaam'       => $jurylid->getVoornaam(),
                                'achternaam'     => $jurylid->getAchternaam(),
                                'contactpersoon' => $user->getVoornaam() . ' ' . $user->getAchternaam(),
                                'vereniging'     => $user->getVereniging()->getNaam() . ', ' .
                                    $user->getVereniging()->getPlaats(),
                                'contactEmail'   => $user->getEmail(),
                            ];
                            $this->sendEmail($subject, $to, $view, $parameters, 'jury@haagsedonarcup.nl');

                            return $this->redirectToRoute('getContactpersoonIndexPage');
                        }
                    }
                }
            }
            return $this->render('contactpersoon/addJury.html.twig', array(
                'menuItems' => $this->menuItems,
                'sponsors' => $this->sponsors,
                'jury' => $jury,
                'classNames' => $classNames,
                'csrfToken' => $csrfToken,
            ));
        } else {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/editJury/{juryId}/", name="editJury")
     * @Method({"GET", "POST"})
     */
    public function editJury(Request $request, $juryId)
    {
        if ($this->wijzigJuryToegestaan()) {
            $this->setBasicPageData();
            /** @var Jurylid $result */
            $result = $this->getDoctrine()->getRepository('AppBundle:Jurylid')
                ->findOneBy(['id' => $juryId]);
            if (!$result) {
                $this->addFlash(
                    'error',
                    'Jurylid niet gevonden'
                );
                return $this->redirectToRoute('getContactpersoonIndexPage');
            } elseif ($result->getUser() != $this->getUser()) {
                $this->addFlash(
                    'error',
                    'Not authorized!'
                );
                return $this->redirectToRoute('getContactpersoonIndexPage');
            } else {
                $jury = [
                    'voornaam' => $result->getVoornaam(),
                    'achternaam' => $result->getAchternaam(),
                    'email' => $result->getEmail(),
                    'brevet' => $result->getBrevet(),
                    'opmerking' => $result->getOpmerking(),
                    'dag' => $this->getBeschikbareDag($result),
                ];
                $classNames = [
                    'voornaam' => 'text',
                    'achternaam' => 'text',
                    'email' => 'text',
                    'brevet' => 'turnster_niveau',
                    'opmerking' => 'text',
                    'dag' => 'turnster_niveau',
                ];
                $csrfToken = $this->getToken();
                if ($request->getMethod() == 'POST') {
                    $jury = [
                        'voornaam' => $request->request->get('voornaam'),
                        'achternaam' => $request->request->get('achternaam'),
                        'email' => $request->request->get('email'),
                        'brevet' => $request->request->get('brevet'),
                        'dag' => $request->request->get('dag'),
                        'opmerking' => $request->request->get('opmerking'),
                    ];
                    $postedToken = $request->request->get('csrfToken');
                    if (!empty($postedToken)) {
                        if ($this->isTokenValid($postedToken)) {
                            $validationJury = [
                                'voornaam' => false,
                                'achternaam' => false,
                                'email' => false,
                                'brevet' => false,
                                'dag' => false,
                                'opmerking' => true,
                            ];

                            $classNames['opmerking'] = 'succesIngevuld';

                            if (strlen($request->request->get('voornaam')) > 1) {
                                $validationJury['voornaam'] = true;
                                $classNames['voornaam'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige voornaam ingevoerd'
                                );
                                $classNames['voornaam'] = 'error';
                            }

                            if (strlen($request->request->get('achternaam')) > 1) {
                                $validationJury['achternaam'] = true;
                                $classNames['achternaam'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige achternaam ingevoerd'
                                );
                                $classNames['achternaam'] = 'error';
                            }

                            if (strlen($request->request->get('email')) > 1) {
                                $emailConstraint = new EmailConstraint();
                                $errors = $this->get('validator')->validate(
                                    $request->request->get('email'),
                                    $emailConstraint
                                );
                                if (count($errors) == 0) {
                                    $validationJury['email'] = true;
                                    $classNames['email'] = 'succesIngevuld';
                                } else {
                                    foreach ($errors as $error) {
                                        $this->addFlash(
                                            'error',
                                            $error->getMessage()
                                        );
                                    }
                                    $classNames['email'] = 'error';
                                }
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen email ingevoerd'
                                );
                                $classNames['email'] = 'error';
                            }

                            if ($request->request->get('brevet')) {
                                $validationJury['brevet'] = true;
                                $classNames['brevet'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen brevet ingevoerd'
                                );
                                $classNames['brevet'] = 'error';
                            }

                            if ($request->request->get('dag')) {
                                $validationJury['dag'] = true;
                                $classNames['dag'] = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen dag ingevoerd'
                                );
                                $classNames['dag'] = 'error';
                            }
                            if (!(in_array(false, $validationJury))) {
                                $jurylid = $result;
                                $jurylid->setVoornaam(trim($request->request->get('voornaam')));
                                $jurylid->setAchternaam(trim($request->request->get('achternaam')));
                                $jurylid->setEmail($request->request->get('email'));
                                $jurylid->setBrevet($request->request->get('brevet'));
                                $jurylid->setOpmerking($request->request->get('opmerking'));
                                $this->setJurylidBeschikbareDagenFromPostData($request->request->get('dag'), $jurylid);
                                $this->addToDB($jurylid);
                                $this->addFlash(
                                    'success',
                                    'Gegevens succesvol gewijzigd!'
                                );
                                return $this->redirectToRoute('getContactpersoonIndexPage');
                            }
                        }
                    }
                }
                return $this->render('contactpersoon/editJury.html.twig', array(
                    'menuItems' => $this->menuItems,
                    'sponsors' => $this->sponsors,
                    'jury' => $jury,
                    'classNames' => $classNames,
                    'csrfToken' => $csrfToken,
                ));
            }
        } else {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/removeJury/", name="removeJury")
     * @Method({"POST"})
     */
    public
    function removeJury(
        Request $request
    ) {
        /** @var Jurylid $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:Jurylid')
            ->findOneBy(['id' => $request->request->get('juryId')]);
        if (!$result) {
            $this->addFlash(
                'error',
                'Jurylid niet gevonden'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
        if ($result->getUser() != $this->getUser()) {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        } else {
            if ($this->wijzigJuryToegestaan()) {
                $this->removeFromDB($result);
                $this->addFlash(
                    'success',
                    'Jurylid succesvol afgemeld!'
                );
            } else {
                $this->addFlash(
                    'error',
                    'Not authorized!'
                );
                return $this->redirectToRoute('getContactpersoonIndexPage');
            }

            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/editContactPassword/", name="editContactPassword")
     * @Method({"GET", "POST"})
     */
    public function editContactPassword(Request $request)
    {
        if (true) {
            $error = false;
            if ($request->getMethod() == 'POST') {
                if ($request->request->get('pass1') != $request->request->get('pass2')) {
                    $this->addFlash(
                        'error',
                        'De wachtwoorden zijn niet gelijk'
                    );
                    $error = true;
                }
                if (strlen($request->request->get('pass1')) < 6) {
                    $this->addFlash(
                        'error',
                        'Het wachtwoord moet minimaal 6 karakters bevatten'
                    );
                    $error = true;
                }
                if (strlen($request->request->get('pass1')) > 20) {
                    $this->addFlash(
                        'error',
                        'Het wachtwoord mag maximaal 20 karakters bevatten'
                    );
                    $error = true;
                }
                if (!($error)) {
                    $userObject = $this->getUser();
                    $password = $request->request->get('pass1');
                    $encoder = $this->container
                        ->get('security.encoder_factory')
                        ->getEncoder($userObject);
                    $userObject->setPassword($encoder->encodePassword($password, $userObject->getSalt()));
                    $this->addToDB($userObject);
                    $this->addFlash(
                        'success',
                        'Het wachtwoord is succesvol gewijzigd'
                    );
                    return $this->redirectToRoute('getContactpersoonIndexPage');
                }
            }
            $csrfToken = $this->getToken();
            $this->setBasicPageData();
            return $this->render('contactpersoon/editPassword.html.twig', array(
                'menuItems' => $this->menuItems,
                'sponsors' => $this->sponsors,
                'csrfToken' => $csrfToken,
            ));
        }
    }

    /**
     * @Template()
     * @Route("/contactpersoon/addVloermuziek/{turnsterId}/", name="addVloermuziek")
     * @Method({"GET", "POST"})
     */
    public function addVloermuziekAction(Request $request, $turnsterId)
    {
        $this->setBasicPageData();
        /** @var Turnster $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->findOneBy(['id' => $turnsterId]);
        if (!$result) {
            $this->addFlash(
                'error',
                'Turnster niet gevonden'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        } elseif ($result->getUser() != $this->getUser()) {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        } else {
            $turnster = [
                'id' => $result->getId(),
                'naam' => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'vloermuziek' => $result->getVloermuziek(),
            ];
            $vloermuziek = new Vloermuziek();
            $form = $this->createFormBuilder($vloermuziek)
                ->add('file')
                ->add('uploadBestand', 'submit')
                ->getForm();
            $form->handleRequest($request);

            if ($form->isValid()) {
                $extensions = array('mp3', 'wma');
                if (in_array(strtolower($vloermuziek->getFile()->getClientOriginalExtension()), $extensions)) {
                    if ($oud = $result->getVloermuziek()) {
                        $this->removeFromDB($oud);
                    }
                    $vloermuziek->setTurnster($result);
                    $result->setVloermuziek($vloermuziek);
                    $this->addToDB($result);
                    $this->addFlash(
                        'success',
                        'Vloermuziek geupload!'
                    );
                    return $this->redirectToRoute('getContactpersoonIndexPage');
                } else {
                    $this->addFlash(
                        'error',
                        'Please upload a valid audio file: mp3 or wma!'
                    );
                }
            }
            return $this->render('contactpersoon/addVloermuziek.html.twig', array(
                'menuItems' => $this->menuItems,
                'sponsors' => $this->sponsors,
                'form' => $form->createView(),
                'turnster' => $turnster,
            ));
        }
    }

    private function getWedstrijdindelingen()
    {
        /** @var ScoresRepository $repo */
        $repo = $this->getDoctrine()->getRepository('AppBundle:Scores');
        $dagen = $repo->getDagenForUser($this->getUser()->getId());
        usort($dagen, function($a, $b) {
            if ($a['wedstrijddag'] == $b['wedstrijddag']) {
                return 0;
            }
            return ($a['wedstrijddag'] < $b['wedstrijddag']) ? -1 : 1;
        });
        $banen = [];
        $wedstrijdrondes = [];
        $categorieNiveau = [];
        foreach ($dagen as $dag) {
            $banen[$dag['wedstrijddag']] = $repo->getBanenPerDagForUser($dag['wedstrijddag'], $this->getUser()
                ->getId());
            $wedstrijdrondes[$dag['wedstrijddag']] = $repo->getWedstrijdrondesPerDagForUser($dag['wedstrijddag'],
                $this->getUser()->getId());
            foreach ($banen[$dag['wedstrijddag']] as $baan) {
                foreach ($wedstrijdrondes[$dag['wedstrijddag']] as $wedstrijdronde) {
                    $categorieNiveau[$dag['wedstrijddag']][$wedstrijdronde['wedstrijdronde']][$baan['baan']] =
                        $repo->getNiveausPerDagPerRondePerBaanForUser($dag['wedstrijddag'],
                            $wedstrijdronde['wedstrijdronde'], $baan['baan'], $this->getUser()->getId());

                }
            }
        }
        return [
            'dagen' => $dagen,
            'banen' => $banen,
            'wedstrijdrondes' => $wedstrijdrondes,
            'categorieNiveau' => $categorieNiveau,
        ];
    }
}
