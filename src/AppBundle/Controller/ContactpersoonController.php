<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Betaling;
use AppBundle\Entity\Jurylid;
use AppBundle\Entity\Scores;
use AppBundle\Entity\ScoresRepository;
use AppBundle\Entity\Team;
use AppBundle\Entity\ToegestaneNiveaus;
use AppBundle\Entity\Turnster;
use AppBundle\Entity\TurnsterRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\Vloermuziek;
use AppBundle\Entity\WedstrijdRonde;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * @Security("has_role('ROLE_CONTACT')")
 */
class ContactpersoonController extends BaseController
{
    /**
     * @Route("/contactpersoon/", name="getContactpersoonIndexPage", methods={"GET"})
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getIndexPageAction()
    {
        $user = $this->getUser();

        $uploadenVloermuziekToegestaan = $this->uploadenVloermuziekToegestaan();
        $wijzigenTurnsterToegestaan    = $this->wijzigTurnsterToegestaan();
        $verwijderenTurnsterToegestaan = $this->verwijderenTurnsterToegestaan();
        $wijzigJuryToegestaan          = $this->wijzigJuryToegestaan();
        $verwijderJuryToegestaan       = $this->wijzigJuryToegestaan();
        $factuurBekijkenToegestaan     = $this->factuurBekijkenToegestaan();
        $this->setBasicPageData();
        /** @var User $user */

        $afgemeldAantal   = 0;
        $wachtlijstAantal = 0;

        /** @var Team $team */
        foreach ($user->getTeams() as $team) {
            if ($team->getWachtlijst()) {
                $wachtlijstAantal++;
            }
            if ($team->isAfgemeld()) {
                $afgemeldAantal++;
            }
        }

        $teLeverenJuryleden = ceil($user->getTeams()->count() / 2);
        if (($juryBoete = $teLeverenJuryleden - $user->getJurylid()->count()) < 0) {
            $juryBoete = 0;
        }
        $teBetalenBedrag = $user->getTeams()->count(
            ) * BaseController::BEDRAG_PER_TEAM + $juryBoete * BaseController::JURY_BOETE_BEDRAG;
        /** @var Betaling[] $betalingen */
        $betalingen    = $user->getBetaling();
        $betaaldBedrag = 0;
        if (count($betalingen) == 0) {
            $factuurId = 'factuur';
        } else {
            foreach ($betalingen as $betaling) {
                $betaaldBedrag += $betaling->getBedrag();
            }
            if ($betaaldBedrag < $teBetalenBedrag) {
                $factuurId = 'factuur_deel';
            } else {
                $factuurId = 'factuur_voldaan';
            }
        }
        return $this->render(
            'contactpersoon/contactpersoonIndex.html.twig',
            array(
                'menuItems'                     => $this->menuItems,
                'sponsors'                      => $this->sponsors,
                'wijzigenTeamToegestaan'        => $wijzigenTurnsterToegestaan,
                'verwijderenTeamToegestaan'     => $verwijderenTurnsterToegestaan,
                'wijzigJuryToegestaan'          => $wijzigJuryToegestaan,
                'verwijderJuryToegestaan'       => $verwijderJuryToegestaan,
                'uploadenVloermuziekToegestaan' => $uploadenVloermuziekToegestaan,
                'factuurBekijkenToegestaan'     => $factuurBekijkenToegestaan,
                'factuurId'                     => $factuurId,
                'user'                          => $user,
                'afgemeldAantal'                => $afgemeldAantal,
                'wachtlijstAantal'              => $wachtlijstAantal,
            )
        );
    }

    /**
     * @Route("/contactpersoon/addTeam/", name="addTeam", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addTeam(Request $request)
    {
        $wedstrijdRondeRepository = $this->getDoctrine()->getRepository('AppBundle:WedstrijdRonde');

        if ($request->getMethod() == 'POST') {
            $postedToken = $request->request->get('csrfToken');
            if (!empty($postedToken)) {
                if ($this->isTokenValid($postedToken)) {
                    $contactpersoon = $this->getUser();

                    $wedstrijdRonde = $wedstrijdRondeRepository->find($request->request->get('wedstrijdronde'));
                    $team           = new Team();
                    $team->setWachtlijst(false);
                    if ($wedstrijdRonde->getMaxTeams() - $wedstrijdRonde->getTeams()->count() <= 0) {
                        $team->setWachtlijst(true);
                    }
                    $team->setUser($contactpersoon);
                    $contactpersoon->addTeam($team);
                    $team->setWedstrijdRonde($wedstrijdRonde);
                    $wedstrijdRonde->addTeam($team);

                    for ($j = 0; $j < 4; $j++) {
                        $turnster = new Turnster();
                        $scores   = new Scores();
                        $turnster->setWachtlijst($team->getWachtlijst());

                        $turnster->setCreationDate(new \DateTime('now'));
                        $turnster->setExpirationDate(
                            new \DateTime(
                                'now + 20 minutes'
                            )
                        );
                        $turnster->setScores($scores);
                        $turnster->setUser($contactpersoon);
                        $turnster->setTeam($team);
                        $contactpersoon->addTurnster($turnster);
                        $team->addTurnster($turnster);
                    }
                    $this->addToDB($contactpersoon);

                    return $this->redirectToRoute('getContactpersoonIndexPage');
                }
            }
        }

        $this->setBasicPageData();

        /** @var WedstrijdRonde[] $wedstrijdRondes */
        $wedstrijdRondes = $wedstrijdRondeRepository->findBy(
            [],
            ['startTijd' => 'asc', 'ronde' => 'asc', 'baan' => 'asc']
        );
        $csrfToken       = $this->getToken();

        return $this->render(
            'contactpersoon/addTeam.html.twig',
            array(
                'menuItems'       => $this->menuItems,
                'sponsors'        => $this->sponsors,
                'csrfToken'       => $csrfToken,
                'wedstrijdRondes' => $wedstrijdRondes,
            )
        );
    }

    /**
     * @Route("/contactpersoon/uitslagen/", name="contactpersoonUitslagen", methods={"GET"})
     */
    public function contactpersoonUitslagen()
    {
        /** @var TurnsterRepository $repo */
        $repo    = $this->getDoctrine()->getRepository('AppBundle:Turnster');
        $catNivs = $repo->getDistinctCatNiv($this->getUser()->getId());
        $pdf     = new UitslagenPdfController('L', 'mm', 'A4');
        foreach ($catNivs as $catNiv) {
            $check = $this->getDoctrine()->getRepository('AppBundle:ToegestaneNiveaus')
                ->findOneBy(
                    [
                        'categorie'           => $catNiv['categorie'],
                        'niveau'              => $catNiv['niveau'],
                        'uitslagGepubliceerd' => 1,
                    ]
                );
            if ($check) {
                /** @var Turnster[] $results */
                $results   = $this->getDoctrine()->getRepository("AppBundle:Turnster")
                    ->getIngeschrevenTurnstersCatNiveau($catNiv['categorie'], $catNiv['niveau']);
                /** @var ToegestaneNiveaus $toegestaneNiveau */
                $toegestaneNiveau      = $this->getDoctrine()->getRepository(ToegestaneNiveaus::class)
                    ->findOneBy(
                        [
                            'categorie' => $catNiv['categorie'],
                            'niveau'    => $catNiv['niveau'],
                        ]
                    );
                $turnsters = [];
                foreach ($results as $result) {
                    $turnsters[] = $result->getUitslagenLijst(
                        $toegestaneNiveau->getCalculationMethodSprongMeerkamp(),
                        $toegestaneNiveau->getCalculationMethodSprongToestelPrijs()
                    );
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
        return new BinaryFileResponse(
            $pdf->Output(
                'Uitslagen ' . $this->getUser()->getVereniging()->getNaam() . ' ' . $this->getUser()->getVereniging()
                    ->getPlaats() . ' DTC ' . self::DATUM_DTC . ".pdf",
                "I"
            ), 200, [
                'Content-Type' => 'application/pdf'
            ]
        );
    }

    /**
     * @Route("/contactpersoon/addTurnster/", name="addTurnster", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addTurnster(Request $request)
    {
        if ($this->wijzigTurnsterToegestaan()) {
            $this->setBasicPageData();
            $turnster      = [
                'voornaam'     => '',
                'achternaam'   => '',
                'geboortejaar' => '',
                'niveau'       => '',
                'opmerking'    => '',
            ];
            $classNames    = [
                'voornaam'     => 'text',
                'achternaam'   => 'text',
                'geboortejaar' => 'turnster_niveau',
                'niveau'       => 'turnster_niveau',
                'opmerking'    => 'text',
            ];
            $geboorteJaren = $this->getGeboorteJaren();
            $vrijePlekken  = $this->getVrijePlekken();
            $csrfToken     = $this->getToken();
            if ($request->getMethod() == 'POST') {
                $turnster    = [
                    'voornaam'     => $request->request->get('voornaam'),
                    'achternaam'   => $request->request->get('achternaam'),
                    'geboortejaar' => $request->request->get('geboorteJaar'),
                    'niveau'       => $request->request->get('niveau'),
                    'opmerking'    => $request->request->get('opmerking'),
                ];
                $postedToken = $request->request->get('csrfToken');
                if (!empty($postedToken)) {
                    if ($this->isTokenValid($postedToken)) {
                        $validationTurnster = [
                            'voornaam'     => false,
                            'achternaam'   => false,
                            'geboorteJaar' => false,
                            'niveau'       => false,
                            'opmerking'    => true,
                        ];

                        $classNames['opmerking'] = 'succesIngevuld';

                        if (strlen($request->request->get('voornaam')) > 1) {
                            $validationTurnster['voornaam'] = true;
                            $classNames['voornaam']         = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige voornaam ingevoerd'
                            );
                            $classNames['voornaam'] = 'error';
                        }

                        if (strlen($request->request->get('achternaam')) > 1) {
                            $validationTurnster['achternaam'] = true;
                            $classNames['achternaam']         = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige achternaam ingevoerd'
                            );
                            $classNames['achternaam'] = 'error';
                        }
                        if ($request->request->get('geboorteJaar')) {
                            $validationTurnster['geboorteJaar'] = true;
                            $classNames['geboortejaar']         = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geboortejaar ingevoerd'
                            );
                            $classNames['geboortejaar'] = 'error';
                        }

                        if ($request->request->get('niveau')) {
                            $validationTurnster['niveau'] = true;
                            $classNames['niveau']         = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen niveau ingevoerd'
                            );
                            $classNames['niveau'] = 'error';
                        }
                        if (!(in_array(false, $validationTurnster))) {
                            $turnster = new Turnster();
                            $scores   = new Scores();
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
            return $this->render(
                'contactpersoon/addTurnster.html.twig',
                array(
                    'menuItems'     => $this->menuItems,
                    'sponsors'      => $this->sponsors,
                    'vrijePlekken'  => $vrijePlekken,
                    'turnster'      => $turnster,
                    'geboorteJaren' => $geboorteJaren,
                    'classNames'    => $classNames,
                    'csrfToken'     => $csrfToken,
                )
            );
        } else {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/editTeam/{teamId}/", name="editTeam", methods={"GET", "POST"})
     * @param Request $request
     * @param         $teamId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editTeam(Request $request, $teamId)
    {
        if ($this->wijzigTurnsterToegestaan()) {
            $this->setBasicPageData();
            /** @var Team $team */
            $team = $this->getDoctrine()->getRepository('AppBundle:Team')
                ->findOneBy(['id' => $teamId]);
            if (!$team) {
                $this->addFlash(
                    'error',
                    'Team niet gevonden'
                );
                return $this->redirectToRoute('getContactpersoonIndexPage');
            } elseif ($team->getUser() != $this->getUser()) {
                $this->addFlash(
                    'error',
                    'Not authorized!'
                );
                return $this->redirectToRoute('getContactpersoonIndexPage');
            } else {
                $csrfToken = $this->getToken();
                if (!$team->getTeamSoort()) {
                    $wedstrijdRonde = $team->getWedstrijdRonde();
                    $teamSoorten    = $wedstrijdRonde->getTeamSoorten();
                    if ($teamSoorten->count() > 1) {
                        return $this->render(
                            'contactpersoon/setTeamSoort.html.twig',
                            array(
                                'menuItems' => $this->menuItems,
                                'sponsors'  => $this->sponsors,
                                'team'      => $team,
                                'csrfToken' => $csrfToken,
                            )
                        );
                    }

                    $team->setTeamSoort($teamSoorten->first());
                    $this->addToDB($team);
                }
                $toegestaneCombinatiesNiveauGeboortejaar = [];
                $toegestaneNiveaus                       = $team->getTeamSoort()->getNiveaus();
                /** @var ToegestaneNiveaus $toegestaneNiveau */
                foreach ($toegestaneNiveaus as $toegestaneNiveau) {
                    $toegestaneCombinatiesNiveauGeboortejaar[] = [
                        'id'           => $toegestaneNiveau->getId(),
                        'categorie'    => $toegestaneNiveau->getCategorie(),
                        'niveau'       => $toegestaneNiveau->getNiveau(),
                    ];
                }
                if ($request->getMethod() == 'POST') {
                    $postedToken = $request->request->get('csrfToken');
                    if (!empty($postedToken) && $this->isTokenValid($postedToken)) {
                        $toegestaneNiveauRepository = $this->getDoctrine()->getRepository(
                            'AppBundle:ToegestaneNiveaus'
                        );

                        if ($request->request->get('teamName')) {
                            $team->setName($request->request->get('teamName'));
                            $this->addToDB($team);
                        }
                        /** @var Turnster $turnster */
                        foreach ($team->getTurnsters() as $turnster) {
                            if (
                                !$request->request->get('turnster_voornaam_' . $turnster->getId()) &&
                                !$request->request->get('turnster_achternaam_' . $turnster->getId()) &&
                                !$request->request->get('niveau_turnster_' . $turnster->getId())
                            ) {
                                $this->emptyTurnster($turnster);
                            }
                            if (
                                $request->request->get('turnster_voornaam_' . $turnster->getId()) ||
                                $request->request->get('turnster_achternaam_' . $turnster->getId()) ||
                                $request->request->get('niveau_turnster_' . $turnster->getId())
                            ) {
                                if (!
                                (
                                    $request->request->get('turnster_voornaam_' . $turnster->getId()) &&
                                    $request->request->get('turnster_achternaam_' . $turnster->getId()) &&
                                    $request->request->get('niveau_turnster_' . $turnster->getId())
                                )
                                ) {
                                    $this->addFlash('error', 'De turnster moet volledig ingevuld zijn!');
                                    return $this->render(
                                        'contactpersoon/editTeam.html.twig',
                                        array(
                                            'menuItems'                               => $this->menuItems,
                                            'sponsors'                                => $this->sponsors,
                                            'toegestaneCombinatiesNiveauGeboortejaar' => $toegestaneCombinatiesNiveauGeboortejaar,
                                            'team'                                    => $team,
                                            'csrfToken'                               => $csrfToken,
                                        )
                                    );
                                }
                                $turnster->setVoornaam(
                                    $request->request->get('turnster_voornaam_' . $turnster->getId())
                                );
                                $turnster->setAchternaam(
                                    $request->request->get('turnster_achternaam_' . $turnster->getId())
                                );

                                $toegestaneNiveau = $toegestaneNiveauRepository->find(
                                    $request->request->get('niveau_turnster_' . $turnster->getId())
                                );

                                if (!$toegestaneNiveau || !$team->getTeamSoort()->getNiveaus()->contains(
                                        $toegestaneNiveau
                                    )) {
                                    $this->addFlash('error', 'Niet overal is een geldig niveau ingevuld');
                                    return $this->render(
                                        'contactpersoon/editTeam.html.twig',
                                        array(
                                            'menuItems'                               => $this->menuItems,
                                            'sponsors'                                => $this->sponsors,
                                            'toegestaneCombinatiesNiveauGeboortejaar' => $toegestaneCombinatiesNiveauGeboortejaar,
                                            'team'                                    => $team,
                                            'csrfToken'                               => $csrfToken,
                                        )
                                    );
                                }

                                $turnster->setCategorie($toegestaneNiveau->getCategorie());
                                $turnster->setNiveau($toegestaneNiveau->getNiveau());
                                $turnster->setIngevuld(true);
                                $this->addToDB($turnster);
                            }
                        }
                        $this->addFlash(
                            'success',
                            'Gegevens succesvol gewijzigd!'
                        );
                        return $this->redirectToRoute('getContactpersoonIndexPage');
                    }
                }
                return $this->render(
                    'contactpersoon/editTeam.html.twig',
                    array(
                        'menuItems'                               => $this->menuItems,
                        'sponsors'                                => $this->sponsors,
                        'toegestaneCombinatiesNiveauGeboortejaar' => $toegestaneCombinatiesNiveauGeboortejaar,
                        'team'                                    => $team,
                        'csrfToken'                               => $csrfToken,
                    )
                );
            }
        } else {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    private function emptyTurnster(Turnster $turnster): void
    {
        $turnster->setVoornaam('leeg');
        $turnster->setAchternaam('leeg');
        $turnster->setCategorie('leeg');
        $turnster->setNiveau('leeg');
        $turnster->setGeboortejaar(0);
        $turnster->setIngevuld(false);

        if ($turnster->getVloermuziek()) {
            $turnster->getVloermuziek()->removeUpload();
            $this->removeFromDB($turnster->getVloermuziek());
            $turnster->setVloermuziek(null);
        }

        $this->addToDB($turnster);
    }

    /**
     * @Route("/contactpersoon/removeTurnster/", name="removeTurnster", methods={"POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @Route("/contactpersoon/removeTeam/", name="removeTeam", methods={"POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeTeam(Request $request)
    {
        /** @var Team $team */
        $team = $this->getDoctrine()->getRepository('AppBundle:Team')
            ->findOneBy(['id' => $request->request->get('teamId')]);
        if (!$team) {
            $this->addFlash(
                'error',
                'Team niet gevonden'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
        if ($team->getUser() != $this->getUser()) {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        } else {
            if ($this->wijzigTurnsterToegestaan() || $team->getWachtlijst()) {
                foreach ($team->getTurnsters() as $turnster) {
                    $this->removeFromDB($turnster);
                }
                /** @var User $user */
                $user = $team->getUser();
                $user->removeTeam($team);
                $wedstrijdRonde = $team->getWedstrijdRonde();
                $wedstrijdRonde->removeTeam($team);
                $this->removeFromDB($team);
                $this->addToDB($user);
                $this->addToDB($wedstrijdRonde);
                if (!$team->getWachtlijst()) {
                    $this->updateWachtlijst($wedstrijdRonde);
                }
            } else {
                $team->setAfgemeld(true);
                foreach ($team->getTurnsters() as $turnster) {
                    $turnster->setAfgemeld(true);
                    $this->addToDB($turnster);
                }
                $this->addToDB($team);
                $wedstrijdRonde = $team->getWedstrijdRonde();
                if (!$team->getWachtlijst()) {
                    $this->updateWachtlijst($wedstrijdRonde);
                }
            }
            $this->addFlash(
                'success',
                'Team succesvol afgemeld!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/removeTurnsterData/", name="removeTurnsterData", methods={"POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeTurnsterData(Request $request)
    {
        /** @var Turnster $turnster */
        $turnster = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->findOneBy(['id' => $request->request->get('turnsterId')]);
        if (!$turnster) {
            $this->addFlash(
                'error',
                'Team niet gevonden'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
        if ($turnster->getUser() != $this->getUser()) {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        } else {
            $this->emptyTurnster($turnster);
            $this->addFlash(
                'success',
                'Turnster succesvol verwijderd'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/addJury/", name="addJury", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addJury(Request $request)
    {
        if ($this->wijzigJuryToegestaan()) {
            $this->setBasicPageData();
            $jury       = [
                'voornaam'   => '',
                'achternaam' => '',
                'email'      => '',
                'brevet'     => '',
                'opmerking'  => '',
                'dag'        => '',
            ];
            $classNames = [
                'voornaam'   => 'text',
                'achternaam' => 'text',
                'email'      => 'text',
                'brevet'     => 'turnster_niveau',
                'opmerking'  => 'text',
                'dag'        => 'turnster_niveau',
            ];
            $csrfToken  = $this->getToken();
            if ($request->getMethod() == 'POST') {
                $jury        = [
                    'voornaam'   => $request->request->get('voornaam'),
                    'achternaam' => $request->request->get('achternaam'),
                    'email'      => $request->request->get('email'),
                    'brevet'     => $request->request->get('brevet'),
                    'dag'        => $request->request->get('dag'),
                    'opmerking'  => $request->request->get('opmerking'),
                ];
                $postedToken = $request->request->get('csrfToken');
                if (!empty($postedToken)) {
                    if ($this->isTokenValid($postedToken)) {
                        $validationJury = [
                            'voornaam'   => false,
                            'achternaam' => false,
                            'email'      => false,
                            'brevet'     => false,
                            'dag'        => false,
                            'opmerking'  => true,
                        ];

                        $classNames['opmerking'] = 'succesIngevuld';

                        if (strlen($request->request->get('voornaam')) > 1) {
                            $validationJury['voornaam'] = true;
                            $classNames['voornaam']     = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige voornaam ingevoerd'
                            );
                            $classNames['voornaam'] = 'error';
                        }

                        if (strlen($request->request->get('achternaam')) > 1) {
                            $validationJury['achternaam'] = true;
                            $classNames['achternaam']     = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige achternaam ingevoerd'
                            );
                            $classNames['achternaam'] = 'error';
                        }

                        if (strlen($request->request->get('email')) > 1) {
                            $emailConstraint = new EmailConstraint();
                            $errors          = $this->get('validator')->validate(
                                $request->request->get('email'),
                                $emailConstraint
                            );
                            if (count($errors) == 0) {
                                $validationJury['email'] = true;
                                $classNames['email']     = 'succesIngevuld';
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
                            $classNames['brevet']     = 'brevet';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen brevet ingevoerd'
                            );
                            $classNames['brevet'] = 'error';
                        }

                        if ($request->request->get('dag')) {
                            $validationJury['dag'] = true;
                            $classNames['dag']     = 'succesIngevuld';
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
                            $user       = $this->getUser();
                            $subject    = 'Aanmelding Donar Team Cup';
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
                            $this->sendEmail($subject, $to, $view, $parameters, 'info@donarteamcup.nl');

                            return $this->redirectToRoute('getContactpersoonIndexPage');
                        }
                    }
                }
            }
            return $this->render(
                'contactpersoon/addJury.html.twig',
                array(
                    'menuItems'  => $this->menuItems,
                    'sponsors'   => $this->sponsors,
                    'jury'       => $jury,
                    'classNames' => $classNames,
                    'csrfToken'  => $csrfToken,
                )
            );
        } else {
            $this->addFlash(
                'error',
                'Not authorized!'
            );
            return $this->redirectToRoute('getContactpersoonIndexPage');
        }
    }

    /**
     * @Route("/contactpersoon/editJury/{juryId}/", name="editJury", methods={"GET", "POST"})
     * @param Request $request
     * @param         $juryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
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
                $jury       = [
                    'voornaam'   => $result->getVoornaam(),
                    'achternaam' => $result->getAchternaam(),
                    'email'      => $result->getEmail(),
                    'brevet'     => $result->getBrevet(),
                    'opmerking'  => $result->getOpmerking(),
                    'dag'        => $this->getBeschikbareDag($result),
                ];
                $classNames = [
                    'voornaam'   => 'text',
                    'achternaam' => 'text',
                    'email'      => 'text',
                    'brevet'     => 'turnster_niveau',
                    'opmerking'  => 'text',
                    'dag'        => 'turnster_niveau',
                ];
                $csrfToken  = $this->getToken();
                if ($request->getMethod() == 'POST') {
                    $jury        = [
                        'voornaam'   => $request->request->get('voornaam'),
                        'achternaam' => $request->request->get('achternaam'),
                        'email'      => $request->request->get('email'),
                        'brevet'     => $request->request->get('brevet'),
                        'dag'        => $request->request->get('dag'),
                        'opmerking'  => $request->request->get('opmerking'),
                    ];
                    $postedToken = $request->request->get('csrfToken');
                    if (!empty($postedToken)) {
                        if ($this->isTokenValid($postedToken)) {
                            $validationJury = [
                                'voornaam'   => false,
                                'achternaam' => false,
                                'email'      => false,
                                'brevet'     => false,
                                'dag'        => false,
                                'opmerking'  => true,
                            ];

                            $classNames['opmerking'] = 'succesIngevuld';

                            if (strlen($request->request->get('voornaam')) > 1) {
                                $validationJury['voornaam'] = true;
                                $classNames['voornaam']     = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige voornaam ingevoerd'
                                );
                                $classNames['voornaam'] = 'error';
                            }

                            if (strlen($request->request->get('achternaam')) > 1) {
                                $validationJury['achternaam'] = true;
                                $classNames['achternaam']     = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige achternaam ingevoerd'
                                );
                                $classNames['achternaam'] = 'error';
                            }

                            if (strlen($request->request->get('email')) > 1) {
                                $emailConstraint = new EmailConstraint();
                                $errors          = $this->get('validator')->validate(
                                    $request->request->get('email'),
                                    $emailConstraint
                                );
                                if (count($errors) == 0) {
                                    $validationJury['email'] = true;
                                    $classNames['email']     = 'succesIngevuld';
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
                                $classNames['brevet']     = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen brevet ingevoerd'
                                );
                                $classNames['brevet'] = 'error';
                            }

                            if ($request->request->get('dag')) {
                                $validationJury['dag'] = true;
                                $classNames['dag']     = 'succesIngevuld';
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
                return $this->render(
                    'contactpersoon/editJury.html.twig',
                    array(
                        'menuItems'  => $this->menuItems,
                        'sponsors'   => $this->sponsors,
                        'jury'       => $jury,
                        'classNames' => $classNames,
                        'csrfToken'  => $csrfToken,
                    )
                );
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
     * @Route("/contactpersoon/removeJury/", name="removeJury", methods={"POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public
    function removeJury(
        Request $request
    )
    {
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
     * @Route("/contactpersoon/editContactPassword/", name="editContactPassword", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editContactPassword(Request $request)
    {
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
                $password   = $request->request->get('pass1');
                $encoder    = $this->container
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
        return $this->render(
            'contactpersoon/editPassword.html.twig',
            array(
                'menuItems' => $this->menuItems,
                'sponsors'  => $this->sponsors,
                'csrfToken' => $csrfToken,
            )
        );
    }

    /**
     * @Route("/contactpersoon/addVloermuziek/{turnsterId}/", name="addVloermuziek", methods={"GET", "POST"})
     * @param Request $request
     * @param         $turnsterId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
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
            $turnster    = [
                'id'          => $result->getId(),
                'naam'        => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'vloermuziek' => $result->getVloermuziek(),
            ];
            $vloermuziek = new Vloermuziek();
            $form        = $this->createFormBuilder($vloermuziek)
                ->add('file')
                ->add('uploadBestand', SubmitType::class)
                ->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $extensions = array('mp3', 'wma');
                if (in_array(strtolower($vloermuziek->getFile()->getClientOriginalExtension()), $extensions)) {
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
            return $this->render(
                'contactpersoon/addVloermuziek.html.twig',
                array(
                    'menuItems' => $this->menuItems,
                    'sponsors'  => $this->sponsors,
                    'form'      => $form->createView(),
                    'turnster'  => $turnster,
                )
            );
        }
    }

    private function getWedstrijdindelingen()
    {
        /** @var ScoresRepository $repo */
        $repo  = $this->getDoctrine()->getRepository('AppBundle:Scores');
        $dagen = $repo->getDagenForUser($this->getUser()->getId());
        usort(
            $dagen,
            function ($a, $b) {
                if ($a['wedstrijddag'] == $b['wedstrijddag']) {
                    return 0;
                }
                return ($a['wedstrijddag'] < $b['wedstrijddag']) ? -1 : 1;
            }
        );
        $banen           = [];
        $wedstrijdrondes = [];
        $categorieNiveau = [];
        foreach ($dagen as $dag) {
            $banen[$dag['wedstrijddag']]           = $repo->getBanenPerDagForUser(
                $dag['wedstrijddag'],
                $this->getUser()
                    ->getId()
            );
            $wedstrijdrondes[$dag['wedstrijddag']] = $repo->getWedstrijdrondesPerDagForUser(
                $dag['wedstrijddag'],
                $this->getUser()->getId()
            );
            foreach ($banen[$dag['wedstrijddag']] as $baan) {
                foreach ($wedstrijdrondes[$dag['wedstrijddag']] as $wedstrijdronde) {
                    $categorieNiveau[$dag['wedstrijddag']][$wedstrijdronde['wedstrijdronde']][$baan['baan']]
                        = $repo->getNiveausPerDagPerRondePerBaanForUser(
                        $dag['wedstrijddag'],
                        $wedstrijdronde['wedstrijdronde'],
                        $baan['baan'],
                        $this->getUser()->getId()
                    );

                }
            }
        }
        return [
            'dagen'           => $dagen,
            'banen'           => $banen,
            'wedstrijdrondes' => $wedstrijdrondes,
            'categorieNiveau' => $categorieNiveau,
        ];
    }

    private function updateWachtlijst(WedstrijdRonde $wedstrijdRonde)
    {
        if (!$this->wijzigTurnsterToegestaan()) {
            return;
        }
        $geplaatsteTeams = 0;
        /** @var Team $team */
        foreach ($wedstrijdRonde->getTeams() as $team) {
            if ($team->isAfgemeld()) {
                continue;
            }

            if ($team->getWachtlijst()) {
                $team->setWachtlijst(false);
                foreach ($team->getTurnsters() as $turnster) {
                    $turnster->setWachtlijst(false);
                    $this->addToDB($turnster);
                }
                $this->addToDB($team);
            }
            $geplaatsteTeams++;

            if ($geplaatsteTeams == $wedstrijdRonde->getMaxTeams()) {
                return;
            }
        }
    }
}
