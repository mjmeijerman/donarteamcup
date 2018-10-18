<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Jurylid;
use AppBundle\Entity\Scores;
use AppBundle\Entity\Team;
use AppBundle\Entity\Turnster;
use AppBundle\Entity\User;
use AppBundle\Entity\Vereniging;
use AppBundle\Entity\Voorinschrijving;
use AppBundle\Entity\WedstrijdRonde;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;


class InschrijvingController extends BaseController
{
    /**
     * @param User    $user
     * @param Session $session
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function InschrijvenPageDeelTwee(User $user, Session $session, Request $request)
    {
        $aantalJury = (ceil($session->get('aantalTeams') / 2) - count($user->getJurylid()));
        if ($request->getMethod() == 'POST') {
            $postedToken = $request->request->get('csrfToken');
            if (!empty($postedToken)) {
                if ($this->isTokenValid($postedToken)) {
                    if ($request->request->get('ids')) {
                        $ids = explode('.', $request->request->get('ids'));
                        array_pop($ids);
                        foreach ($ids as $id) {
                            $team = $this->getDoctrine()->getRepository('AppBundle:Team')->find($id);
                            $teamSoortId = $request->request->get('team_soort_' . $id);
                            $teamSoort = $this->getDoctrine()->getRepository('AppBundle:TeamSoort')->find($teamSoortId);
                            $team->setTeamSoort($teamSoort);
                            if (!empty($request->request->get('team_name_' . $id))) {
                                $team->setName($request->request->get('team_name_' . $id));
                            }
                            $this->addToDB($team);

                            /** @var Turnster $turnster */
                            foreach ($team->getTurnsters() as $turnster) {
                                if ($request->request->get('voornaam_' . $turnster->getId()) && $request->request->get(
                                        'achternaam_' . $turnster->getId()
                                    ) &&
                                    $request->request->get('geboorteJaar_' . $turnster->getId()) && $request->request->get(
                                        'niveau_' . $turnster->getId()
                                    )
                                ) {
                                    $turnster->setVoornaam(trim($request->request->get('voornaam_' . $turnster->getId())));
                                    $turnster->setAchternaam(trim($request->request->get('achternaam_' . $turnster->getId())));
                                    $turnster->setGeboortejaar($request->request->get('geboorteJaar_' . $turnster->getId()));
                                    $turnster->setNiveau($request->request->get('niveau_' . $turnster->getId()));
                                    $turnster->setCategorie(
                                        $this->getCategorie(
                                            $request->request->get
                                            (
                                                'geboorteJaar_' . $turnster->getId()
                                            )
                                        )
                                    );
                                    $turnster->setExpirationDate(null);
                                    $turnster->setIngevuld(true);
                                    $this->addToDB($turnster);
                                }
                            }
                        }
                        for ($i = 1; $i <= $aantalJury; $i++) {
                            if ($request->request->get('jury_voornaam_' . $i) && $request->request->get(
                                    'jury_achternaam_' . $i
                                )
                                && $request->request->get('jury_email_' . $i) && $request->request->get(
                                    'jury_brevet_' . $i
                                )
                                && $request->request->get('jury_dag_' . $i)
                            ) {
                                $jurylid = new Jurylid();
                                $jurylid->setVoornaam(trim($request->request->get('jury_voornaam_' . $i)));
                                $jurylid->setAchternaam(trim($request->request->get('jury_achternaam_' . $i)));
                                $jurylid->setEmail($request->request->get('jury_email_' . $i));
                                $jurylid->setBrevet($request->request->get('jury_brevet_' . $i));
                                $jurylid->setOpmerking($request->request->get('jury_opmerking_' . $i));
                                $this->setJurylidBeschikbareDagenFromPostData(
                                    $request->request->get('jury_dag_' . $i),
                                    $jurylid
                                );
                                $jurylid->setUser($user);
                                $user->addJurylid($jurylid);
                                $this->addToDB($user);

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
                                $this->sendEmail($subject, $to, $view, $parameters, 'jury@donarteamcup.nl');
                            }
                        }
                    }
                    if ($request->request->get('remove_session')) {
                        $session->clear();
                        return $this->redirectToRoute('getContent', array('page' => 'Laatste nieuws'));
                    }
                }
            }
        }

        $turnsterFields   = [];
        $timeToExpiration = 0;

//        $turnsters = $user->getTurnster();
//        foreach ($turnsters as $turnster) {
//            $turnsterFields[$turnster->getId()] = $turnster->getWachtlijst();
//            if ($timeToExpiration == 0) {
//                $timeToExpiration = floor(($turnster->getExpirationDate()->getTimestamp() - time() - 120) / 60);
//            }
//            if ($timeToExpiration < 0) {
//                $timeToExpiration = 0;
//            }
//        }
        /** @var Jurylid[] $juryleden */
        $tijdTot       = date('d-m-Y H:i', (time() + ($timeToExpiration) * 60));
        $csrfToken     = $this->getToken();
        $optegevenJury = ceil($session->get('aantalTeams') / 2);
        $aantalJury = (ceil($session->get('aantalTeams') / 2) - count($user->getJurylid()));

        return $this->render(
            'inschrijven/inschrijven_turnsters.html.twig',
            array(
                'menuItems'           => $this->menuItems,
                'sponsors'            => $this->sponsors,
                'csrfToken'           => $csrfToken,
                'timeToExpiration'    => $timeToExpiration,
                'turnsterFields'      => $turnsterFields,
                'tijdTot'             => $tijdTot,
                'aantalJury'          => $aantalJury,
                'optegevenJury'       => $optegevenJury,
                'vrijePlekken'        => $session->get('vrijePlekken'),
                'user'                => $user,
            )
        );
    }

    /**
     * @Route("/inschrijven", name="inschrijven", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function inschrijvenPage(Request $request)
    {
//        session_destroy();
//        $repository = $this->getDoctrine()->getRepository('AppBundle:Team');
//
//        $team = $repository->find(20);
//        /** @var Turnster $turnster */
//        foreach ($team->getTurnsters() as $turnster) {
//            $turnster->setTeam(null);
//        }
//        $this->removeFromDB($team);
//
//        $team = $repository->find(21);
//        /** @var Turnster $turnster */
//        foreach ($team->getTurnsters() as $turnster) {
//            $turnster->setTeam(null);
//        }
//        $this->removeFromDB($team);
//
//        $team = $repository->find(22);
//        /** @var Turnster $turnster */
//        foreach ($team->getTurnsters() as $turnster) {
//            $turnster->setTeam(null);
//        }
//        $this->removeFromDB($team);
//
//        $team = $repository->find(19);
//        /** @var Turnster $turnster */
//        foreach ($team->getTurnsters() as $turnster) {
//            $turnster->setTeam(null);
//        }
//        $this->removeFromDB($team);

        $this->updateGereserveerdePlekken();
        $session = new Session();
        if ($this->inschrijvingToegestaan($request->query->get('token'), $session)) {
            $this->setBasicPageData();
            if ($session->get('username') && $user = $this->getDoctrine()->getRepository('AppBundle:User')
                    ->loadUserByUsername($session->get('username'))
            ) {
                return $this->InschrijvenPageDeelTwee($user, $session, $request);
            }
            $display          = "none";
            $verenigingOption = '';
            $values           = [
                'verenigingId'      => '',
                'verenigingsnaam'   => '',
                'verenigingsplaats' => '',
                'voornaam'          => '',
                'achternaam'        => '',
                'email'             => '',
                'telefoonnummer'    => '',
                'username'          => '',
                'wachtwoord'        => '',
                'wachtwoord2'       => '',
                'aantalTurnsters'   => '',
            ];
            $classNames       = [
                'verenigingnaam'                    => 'select',
                'verenigingsnaam'                   => 'text',
                'verenigingsplaats'                 => 'text',
                'voornaam'                          => 'text',
                'achternaam'                        => 'text',
                'email'                             => 'text',
                'telefoonnummer'                    => 'text',
                'username'                          => 'text',
                'wachtwoord'                        => 'text',
                'wachtwoord2'                       => 'text',
                'aantalTurnsters'                   => 'number',
                'inschrijven_vereniging_header'     => '',
                'inschrijven_contactpersoon_header' => '',
                'aantal_plekken_header'             => '',
            ];
            if ($request->getMethod() == 'POST') {
                $display = "";
                if ($request->request->get('verenigingsid')) {
                    $values['verenigingId'] = $request->request->get('verenigingsid');
                } else {
                    $values['verenigingsnaam']   = $request->request->get('verenigingsnaam');
                    $values['verenigingsplaats'] = $request->request->get('verenigingsplaats');;
                    $verenigingOption = 'checked';
                }
                $values['voornaam']        = $request->request->get('voornaam');
                $values['achternaam']      = $request->request->get('achternaam');
                $values['email']           = $request->request->get('email');
                $values['telefoonnummer']  = $request->request->get('telefoonnummer');
                $values['username']        = $request->request->get('username');
                $values['wachtwoord']      = $request->request->get('wachtwoord');
                $values['wachtwoord2']     = $request->request->get('wachtwoord2');
                $values['aantalTurnsters'] = $request->request->get('aantalTurnsters');
                $postedToken               = $request->request->get('csrfToken');
                if (!empty($postedToken)) {
                    if ($this->isTokenValid($postedToken)) {
                        $validationVereniging = [
                            'verengingsId'      => false,
                            'verenigingsnaam'   => false,
                            'verenigingsplaats' => false,
                        ];

                        if ($request->request->get('verenigingsid')) {
                            $validationVereniging['verenigingsnaam']   = true;
                            $validationVereniging['verenigingsplaats'] = true;
                            if ($vereniging = $this->getDoctrine()->getRepository('AppBundle:Vereniging')
                                ->findOneBy(['id' => $request->request->get('verenigingsid')])
                            ) {
                                $validationVereniging['verengingsId'] = true;
                                $classNames['verenigingnaam']         = 'selectIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige vereniging geselecteerd'
                                );
                                $classNames['verenigingnaam'] = 'error';
                            }
                        } else {
                            $vereniging                           = new Vereniging();
                            $validationVereniging['verengingsId'] = true;
                            if (strlen($request->request->get('verenigingsnaam')) > 1) {
                                $validationVereniging['verenigingsnaam'] = true;
                                $classNames['verenigingsnaam']           = 'succesIngevuld';
                                $vereniging->setNaam(trim(strtoupper($request->request->get('verenigingsnaam'))));
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige verenigingsnaam ingevoerd'
                                );
                                $classNames['verenigingsnaam'] = 'error';
                            }
                            if (strlen($request->request->get('verenigingsplaats')) > 1) {
                                $validationVereniging['verenigingsplaats'] = true;
                                $classNames['verenigingsplaats']           = 'succesIngevuld';
                                $vereniging->setPlaats(trim(strtoupper($request->request->get('verenigingsplaats'))));
                            } else {
                                $this->addFlash(
                                    'error',
                                    'geen geldige verenigingsplaats ingevoerd'
                                );
                                $classNames['verenigingsplaats'] = 'error';
                            }
                            if (!(in_array(false, $validationVereniging))) {
                                $this->addToDB($vereniging);
                            }
                        }
                        if (!(in_array(false, $validationVereniging))) {
                            $classNames['inschrijven_vereniging_header'] = 'success';
                        }

                        $validationContactpersoon = [
                            'voornaam'       => false,
                            'achternaam'     => false,
                            'email'          => false,
                            'telefoonnummer' => false,
                            'username'       => false,
                            'wachtwoord'     => false,
                            'wachtwoord2'    => false,
                        ];

                        if (strlen($request->request->get('voornaam')) > 1) {
                            $validationContactpersoon['voornaam'] = true;
                            $classNames['voornaam']               = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige voornaam ingevoerd'
                            );
                            $classNames['voornaam'] = 'error';
                        }

                        if (strlen($request->request->get('achternaam')) > 1) {
                            $validationContactpersoon['achternaam'] = true;
                            $classNames['achternaam']               = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'geen geldige achternaam ingevoerd'
                            );
                            $classNames['achternaam'] = 'error';
                        }

                        $emailConstraint = new EmailConstraint();
                        $errors          = $this->get('validator')->validate(
                            $request->request->get('email'),
                            $emailConstraint
                        );
                        if (count($errors) == 0) {
                            $validationContactpersoon['email'] = true;
                            $classNames['email']               = 'succesIngevuld';
                        } else {
                            foreach ($errors as $error) {
                                $this->addFlash(
                                    'error',
                                    $error->getMessage()
                                );
                            }
                            $classNames['email'] = 'error';
                        }

                        $re = '/^([0-9]+)$/';
                        if (preg_match(
                                $re,
                                $request->request->get('telefoonnummer')
                            ) && strlen($request->request->get('telefoonnummer')) == 10
                        ) {
                            $validationContactpersoon['telefoonnummer'] = true;
                            $classNames['telefoonnummer']               = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'Het telefoonnummer moet uit precies 10 cijfers bestaan'
                            );
                            $classNames['telefoonnummer'] = 'error';
                        }

                        if (strlen($request->request->get('username')) > 1) {
                            if ($this->checkUsernameAvailability($request->request->get('username')) === 'true') {
                                $validationContactpersoon['username'] = true;
                                $classNames['username']               = 'succesIngevuld';
                            } else {
                                $this->addFlash(
                                    'error',
                                    'De inlognaam is al in gebruik'
                                );
                                $classNames['username'] = 'error';
                            }
                        } else {
                            $this->addFlash(
                                'error',
                                'Geen geldige inlognaam ingevoerd'
                            );
                            $classNames['username'] = 'error';
                        }

                        if (strlen($request->request->get('wachtwoord')) > 5) {
                            $validationContactpersoon['wachtwoord']  = true;
                            $classNames['wachtwoord']                = 'succesIngevuld';
                            $validationContactpersoon['wachtwoord2'] = true;
                            $classNames['wachtwoord2']               = 'succesIngevuld';
                        } else {
                            $this->addFlash(
                                'error',
                                'Dit wachtwoord is te kort'
                            );
                            $classNames['wachtwoord'] = 'error';
                        }

                        if ($request->request->get('wachtwoord') != $request->request->get('wachtwoord2')) {
                            $validationContactpersoon['wachtwoordenGelijk'] = false;
                            $this->addFlash(
                                'error',
                                'De wachtwoorden zijn niet aan elkaar gelijk'
                            );
                            $classNames['wachtwoord']  = 'error';
                            $classNames['wachtwoord2'] = 'error';
                        }

                        if (!(in_array(false, $validationContactpersoon))) {
                            $classNames['inschrijven_contactpersoon_header'] = 'success';
                        }

                        $validationAantalTeams = false;
                        /** @var WedstrijdRonde[] $rondes */
                        $rondes = $this->getDoctrine()->getRepository('AppBundle:WedstrijdRonde')->findAll();

                        $aantalTeams = 0;
                        $ingeschrevenRondes = [];
                        foreach ($rondes as $ronde) {
                            if ($request->request->get('aantal_teams_' . $ronde->getId()) >= 0) {
                                $aantalTeams += $request->request->get('aantal_teams_' . $ronde->getId());
                                $ingeschrevenRondes[$ronde->getId()] = $request->request->get('aantal_teams_' . $ronde->getId());
                            }
                        }
                        if ($aantalTeams > 0) {
                            if ($aantalTeams > 20) {
                                $this->addFlash(
                                    'error',
                                    'Je probeert te veel plekken te reserveren!'
                                );
                            } else {
                                $validationAantalTeams           = true;
                                $classNames['aantalTurnsters']       = 'numberIngevuld';
                                $classNames['aantal_plekken_header'] = 'success';
                            }
                        } else {
                            $this->addFlash(
                                'error',
                                'Totaal aantal teams moet groter zijn dan 0!'
                            );
                        }

                        if (!(in_array(false, $validationVereniging)) && !(in_array(
                                false,
                                $validationContactpersoon
                            )) &&
                            $validationAantalTeams
                        ) {
                            if ($request->query->get('token')) {
                                /** @var Voorinschrijving $result */
                                $result = $this->getDoctrine()
                                    ->getRepository('AppBundle:Voorinschrijving')
                                    ->findOneBy(
                                        array('token' => $request->query->get('token'))
                                    );
                                $result->setUsedAt(new \DateTime('now'));
                                $this->addToDB($result);
                                $session->set('token', $request->query->get('token'));
                            }
                            $contactpersoon = new User();
                            $contactpersoon->setUsername($request->request->get('username'));
                            $contactpersoon->setRole('ROLE_CONTACT');
                            $contactpersoon->setEmail($request->request->get('email'));
                            $contactpersoon->setVoornaam(trim($request->request->get('voornaam')));
                            $contactpersoon->setAchternaam(trim($request->request->get('achternaam')));
                            $password = $request->request->get('wachtwoord');
                            $encoder  = $this->container
                                ->get('security.encoder_factory')
                                ->getEncoder($contactpersoon);
                            $contactpersoon->setPassword(
                                $encoder->encodePassword($password, $contactpersoon->getSalt())
                            );
                            $contactpersoon->setIsActive(true);
                            $contactpersoon->setTelefoonnummer($request->request->get('telefoonnummer'));
                            $contactpersoon->setCreatedAt(new \DateTime('now'));
                            $contactpersoon->setVereniging($vereniging);
                            foreach ($ingeschrevenRondes as $id => $number) {
                                for ($i = 0; $i < $number; $i++) {
                                    $wedstrijdRonde = $this->getDoctrine()->getRepository('AppBundle:WedstrijdRonde')->find($id);
                                    $team = new Team();
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
                                }
                            }
                            $session->set('vrijePlekken', $this->getVrijePlekken());
                            $subject        = 'Inloggegevens Donar Team Cup';
                            $to             = $contactpersoon->getEmail();
                            $view           = 'mails/inschrijven_contactpersoon.html.twig';
                            $inschrijvenTot = $this->getOrganisatieInstellingen(self::SLUITING_INSCHRIJVING_TURNSTERS);
                            $parameters     = [
                                'voornaam'       => $contactpersoon->getVoornaam(),
                                'inschrijvenTot' => $inschrijvenTot[self::SLUITING_INSCHRIJVING_TURNSTERS],
                                'inlognaam'      => $contactpersoon->getUsername(),
                            ];
                            $this->sendEmail($subject, $to, $view, $parameters);
                            $session->set('username', $contactpersoon->getUsername());
                            $session->set('aantalTeams', $aantalTeams);
                            return $this->InschrijvenPageDeelTwee($contactpersoon, $session, $request);
                        }
                    }
                }
            }
            $vrijePlekken = $this->getVrijePlekken();
            $verenigingen = $this->getVerenigingen();
            $csrfToken    = $this->getToken();
            $repository   = $this->getDoctrine()->getRepository('AppBundle:WedstrijdRonde');
            /** @var WedstrijdRonde[] $wedstrijdRondes */
            $wedstrijdRondes = $repository->findBy([], ['startTijd' => 'asc', 'ronde' => 'asc', 'baan' => 'asc']);

            return $this->render(
                'inschrijven/inschrijven_contactpersoon.html.twig',
                array(
                    'menuItems'        => $this->menuItems,
                    'sponsors'         => $this->sponsors,
                    'vrijePlekken'     => $vrijePlekken,
                    'verenigingen'     => $verenigingen,
                    'csrfToken'        => $csrfToken,
                    'display'          => $display,
                    'verenigingOption' => $verenigingOption,
                    'classNames'       => $classNames,
                    'values'           => $values,
                    'wedstrijdRondes'  => $wedstrijdRondes,
                )
            );
        } else {
            return $this->redirectToRoute('getContent', array('page' => 'Inschrijvingsinformatie'));
        }
    }

    private function getMinutesToExpiration($aantalTeams)
    {
        if ($aantalTeams > 1) {
            return $aantalTeams * 4;
        } else {
            return 5;
        }
    }

    /**
     * @Route("/checkUsername/{username}/", name="checkUsernameAvailabilityAjaxCall", options={"expose"=true}, methods={"GET"})
     * @param $username
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkUsernameAvailabilityAjaxCall($username)
    {
        return new JsonResponse($this->checkUsernameAvailability($username));
    }

    /**
     * @param $username
     *
     * @return string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function checkUsernameAvailability($username)
    {
        $this->updateGereserveerdePlekken();
        /** @var User[] $users */
        $users     = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();
        $usernames = [];
        foreach ($users as $user) {
            $usernames[] = strtolower($user->getUsername());
        }
        if (in_array(strtolower($username), $usernames)) {
            return 'false';
        } else {
            return 'true';
        }
    }

    /**
     * @Route("/getAvailableNiveausAjaxCall/{teamSoortId}/{geboorteJaar}/", name="getAvailableNiveausAjaxCall",
     * options={"expose"=true}, methods={"GET"})
     * @param $teamSoortId
     * @param $geboorteJaar
     *
     * @return JsonResponse
     */
    public function getAvailableNiveausAjaxCall($teamSoortId, $geboorteJaar)
    {
        return new JsonResponse($this->getAvailableNiveaus($teamSoortId, $geboorteJaar));
    }

    /**
     * @Route("/getAvailableGeboorteJarenAjaxCall/{teamSoortId}/", name="getAvailableGeboorteJarenAjaxCall",
     * options={"expose"=true}, methods={"GET"})
     * @param $teamSoortId
     *
     * @return JsonResponse
     */
    public function getAvailableGeboorteJarenAjaxCall($teamSoortId)
    {
        return new JsonResponse($this->getAvailableGeboortejaren($teamSoortId));
    }
}
