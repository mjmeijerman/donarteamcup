<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Jurylid;
use AppBundle\Entity\Scores;
use AppBundle\Entity\Turnster;
use AppBundle\Entity\User;
use AppBundle\Entity\Vereniging;
use AppBundle\Entity\Voorinschrijving;
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
        $aantalJury = (ceil($session->get('aantalTurnsters') / 10) - count($user->getJurylid()));
        if ($request->getMethod() == 'POST') {
            $postedToken = $request->request->get('csrfToken');
            if (!empty($postedToken)) {
                if ($this->isTokenValid($postedToken)) {
                    if ($request->request->get('ids')) {
                        $ids = explode('.', $request->request->get('ids'));
                        array_pop($ids);
                        foreach ($ids as $id) {
                            if ($request->request->get('voornaam_' . trim($id)) && $request->request->get(
                                    'achternaam_' . trim($id)
                                ) &&
                                $request->request->get('geboorteJaar_' . trim($id)) && $request->request->get(
                                    'niveau_' . trim($id)
                                )
                            ) {
                                /** @var Turnster $turnster */
                                if ($turnster = $this->getDoctrine()->getRepository('AppBundle:Turnster')
                                    ->findOneBy(['id' => trim($id)])
                                ) {
                                    $turnster->setVoornaam(trim($request->request->get('voornaam_' . trim($id))));
                                    $turnster->setAchternaam(trim($request->request->get('achternaam_' . trim($id))));
                                    $turnster->setGeboortejaar($request->request->get('geboorteJaar_' . trim($id)));
                                    $turnster->setNiveau($request->request->get('niveau_' . trim($id)));
                                    $turnster->setCategorie(
                                        $this->getCategorie(
                                            $request->request->get
                                            (
                                                'geboorteJaar_' . trim($id)
                                            )
                                        )
                                    );
                                    $turnster->setExpirationDate(null);
                                    $turnster->setIngevuld(true);
                                    $this->addToDB($turnster);
                                } else {
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
                                    $turnster->setUser($user);
                                    $turnster->setIngevuld(true);
                                    $turnster->setVoornaam(trim($request->request->get('voornaam_' . trim($id))));
                                    $turnster->setAchternaam(trim($request->request->get('achternaam_' . trim($id))));
                                    $turnster->setGeboortejaar($request->request->get('geboorteJaar_' . trim($id)));
                                    $turnster->setNiveau($request->request->get('niveau_' . trim($id)));
                                    $user->addTurnster($turnster);
                                    $this->addToDB($user);
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
        /** @var Turnster[] $turnsters */
        $turnsters = $user->getTurnster();
        if (count($turnsters) < $session->get('aantalTurnsters')) {
            for ($i = 0; $i < ($session->get('aantalTurnsters') - count($turnsters)); $i++) {
                $turnster = new Turnster();
                $scores   = new Scores();
                if ($this->getVrijePlekken() > $i) {
                    $turnster->setWachtlijst(false);
                } else {
                    $turnster->setWachtlijst(true);
                }
                $turnster->setCreationDate(new \DateTime('now'));
                $turnster->setExpirationDate(new \DateTime('now + 2 minutes'));
                $turnster->setScores($scores);
                $turnster->setUser($user);
                $user->addTurnster($turnster);
                $this->addToDB($user);
            }
        }
        $geboorteJaren       = $this->getGeboorteJaren();
        $opgeslagenTurnsters = [];
        foreach ($turnsters as $turnster) {
            if ($turnster->getExpirationDate()) {
                $turnsterFields[$turnster->getId()] = $turnster->getWachtlijst();
                if ($timeToExpiration == 0) {
                    $timeToExpiration = floor(($turnster->getExpirationDate()->getTimestamp() - time() - 120) / 60);
                }
                if ($timeToExpiration < 0) {
                    $timeToExpiration = 0;
                }
            } else {
                $opgeslagenTurnsters[] = [
                    'voornaam'     => $turnster->getVoornaam(),
                    'achternaam'   => $turnster->getAchternaam(),
                    'geboortejaar' => $turnster->getGeboortejaar(),
                    'niveau'       => $turnster->getNiveau(),
                    'wachtlijst'   => $turnster->getWachtlijst(),
                ];
            }
        }
        $opgeslagenJuryleden = [];
        /** @var Jurylid[] $juryleden */
        $juryleden = $user->getJurylid();
        foreach ($juryleden as $jurylid) {
            $opgeslagenJuryleden[] = [
                'voornaam'   => $jurylid->getVoornaam(),
                'achternaam' => $jurylid->getAchternaam(),
                'email'      => $jurylid->getEmail(),
                'brevet'     => $jurylid->getBrevet(),
            ];
        }
        $tijdTot       = date('d-m-Y H:i', (time() + ($timeToExpiration) * 60));
        $csrfToken     = $this->getToken();
        $optegevenJury = ceil($session->get('aantalTurnsters') / 10);
        $aantalJury    = (ceil($session->get('aantalTurnsters') / 10) - count($user->getJurylid()));
        return $this->render(
            'inschrijven/inschrijven_turnsters.html.twig',
            array(
                'menuItems'           => $this->menuItems,
                'sponsors'            => $this->sponsors,
                'csrfToken'           => $csrfToken,
                'timeToExpiration'    => $timeToExpiration,
                'turnsterFields'      => $turnsterFields,
                'tijdTot'             => $tijdTot,
                'geboorteJaren'       => $geboorteJaren,
                'opgeslagenTurnsters' => $opgeslagenTurnsters,
                'aantalJury'          => $aantalJury,
                'opgeslagenJuryleden' => $opgeslagenJuryleden,
                'optegevenJury'       => $optegevenJury,
                'vrijePlekken'        => $session->get('vrijePlekken'),
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

                        $validationAantalturnsters = false;
                        if ($request->request->get('aantalTurnsters') > 0) {
                            if ($request->request->get('aantalTurnsters') > 70) {
                                $this->addFlash(
                                    'error',
                                    'Je probeert te veel plekken te reserveren!'
                                );
                            } else {
                                $validationAantalturnsters           = true;
                                $classNames['aantalTurnsters']       = 'numberIngevuld';
                                $classNames['aantal_plekken_header'] = 'success';
                            }
                        } else {
                            $this->addFlash(
                                'error',
                                'Aantal turnsters moet groter zijn dan 0!'
                            );
                        }

                        if (!(in_array(false, $validationVereniging)) && !(in_array(
                                false,
                                $validationContactpersoon
                            )) &&
                            $validationAantalturnsters
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
                            for ($i = 0; $i < $request->request->get('aantalTurnsters'); $i++) {
                                $turnster = new Turnster();
                                $scores   = new Scores();
                                if ($this->getVrijePlekken() > $i) {
                                    $turnster->setWachtlijst(false);
                                } else {
                                    $turnster->setWachtlijst(true);
                                }
                                $turnster->setCreationDate(new \DateTime('now'));
                                $turnster->setExpirationDate(
                                    new \DateTime(
                                        'now + ' . ($this->getMinutesToExpiration(
                                                $request->request->get('aantalTurnsters')
                                            )
                                            + 3) . 'minutes'
                                    )
                                );
                                $turnster->setScores($scores);
                                $turnster->setUser($contactpersoon);
                                $contactpersoon->addTurnster($turnster);
                            }
                            $session->set('vrijePlekken', $this->getVrijePlekken());
                            $this->addToDB($contactpersoon);
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
                            $session->set('aantalTurnsters', $request->request->get('aantalTurnsters'));
                            return $this->InschrijvenPageDeelTwee($contactpersoon, $session, $request);
                        }
                    }
                }
            }
            $vrijePlekken = $this->getVrijePlekken();
            $verenigingen = $this->getVerenigingen();
            $csrfToken    = $this->getToken();
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
                )
            );
        } else {
            return $this->redirectToRoute('getContent', array('page' => 'Inschrijvingsinformatie'));
        }
    }

    private function getMinutesToExpiration($aantalTurnsters)
    {
        if ($aantalTurnsters > 5) {
            return $aantalTurnsters;
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
     * @Route("/getAvailableNiveausAjaxCall/{geboorteJaar}/", name="getAvailableNiveausAjaxCall",
     * options={"expose"=true}, methods={"GET"})
     * @param $geboorteJaar
     *
     * @return JsonResponse
     */
    public function getAvailableNiveausAjaxCall($geboorteJaar)
    {
        return new JsonResponse($this->getAvailableNiveaus($geboorteJaar));
    }
}
