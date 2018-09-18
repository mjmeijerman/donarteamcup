<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Content;
use AppBundle\Entity\Instellingen;
use AppBundle\Entity\ScoresRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ContentController extends BaseController
{

    /**
     * @Route("/", name="getIndexPage", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->redirectToRoute('getContent', array('page' => 'Laatste nieuws'));
    }

    /**
     * @Route("/pagina/{page}", name="getContent", methods={"GET"})
     * @param $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getContentAction($page)
    {
        $this->setBasicPageData();
        if ($this->checkIfPageExists($page)) {
            switch ($page) {
                case 'Inloggen':
                    return $this->getInloggenPageAction();
                    break;
                case 'Laatste nieuws':
                    return $this->getNieuwsIndexPage();
                case 'Wedstrijdindeling':
                    return $this->getWedstrijdindelingPage();
                case 'Sponsors':
                    return $this->render(
                        'default/sponsors.html.twig',
                        array(
                            'menuItems' => $this->menuItems,
                            'sponsors'  => $this->sponsors,
                        )
                    );
                default:
                    $result = $this->getDoctrine()
                        ->getRepository('AppBundle:Content')
                        ->findBy(
                            array('pagina' => $page),
                            array('gewijzigd' => 'DESC')
                        );
                    if (count($result) > 0) {
                        /** @var Content $result */
                        $result = $result[0];
                    }
                    $content = "";
                    if ($result) {
                        $content = $result->getContent();
                    }
                    return $this->render(
                        'default/index.html.twig',
                        array(
                            'content'   => $content,
                            'menuItems' => $this->menuItems,
                            'sponsors'  => $this->sponsors,
                        )
                    );
            }
        } else {
            return $this->render(
                'error/pageNotFound.html.twig',
                array(
                    'menuItems' => $this->menuItems,
                    'sponsors'  => $this->sponsors,
                )
            );
        }
    }

    private function getInloggenPageAction()
    {
        $user     = $this->getUser();
        $roles[0] = "";
        if ($user) {
            $roles = $user->getRoles();
        }
        switch ($roles[0]) {
            case 'ROLE_ADMIN':
                return $this->redirectToRoute('getAdminIndexPage');
                break;
            case 'ROLE_CONTACT':
                return $this->redirectToRoute('getContactpersoonIndexPage');
                break;
            case 'ROLE_ORGANISATIE':
                return $this->redirectToRoute('organisatieGetContent', array('page' => 'Mijn gegevens'));
                break;
            default:
                return $this->redirectToRoute('login_route');
        }
    }

    /**
     * @Route("/getVrijePlekken/", name="aantalVrijePlekkenAjaxCall", options={"expose"=true}, methods={"GET"})
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function aantalVrijePlekkenAjaxCall()
    {
        $this->updateGereserveerdePlekken();
        return new Response($this->getVrijePlekken());
    }

    /**
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getNieuwsIndexPage()
    {
        $aantalPlekken = -1;
        $tijdVol       = false;
        $tijdTotVol    = false;
        $results       = $this->getDoctrine()
            ->getRepository('AppBundle:Nieuwsbericht')
            ->findBy(
                array(),
                array('id' => 'DESC'),
                10
            );
        if ($this->verwijderenTurnsterToegestaan()) {
            if (!$this->inschrijvingToegestaan()) {
                $aantalPlekken = 0;
            } else {
                $aantalPlekken = $this->getVrijePlekken();
            }
            if ($aantalPlekken == 0) {
                /** @var \DateTime $tijdVolObject */
                $tijdVolObject    = $this->getTijdVol();
                $tijdVol['datum'] = $tijdVolObject->format('d-m-Y');
                $tijdVol['tijd']  = $tijdVolObject->format('H:i:s');
                $result           = $this->getDoctrine()
                    ->getRepository('AppBundle:Instellingen')
                    ->findBy(
                        array('instelling' => self::OPENING_INSCHRIJVING),
                        array('gewijzigd' => 'DESC')
                    );
                $datumGeopend     = 0;
                if (count($result) > 0) {
                    /** @var Instellingen[] $result */
                    /** @var \DateTime $datumGeopend */
                    $datumGeopend = $result[0]->getDatum();
                }
                $timestampVol           = ($tijdVolObject->getTimestamp() - $datumGeopend->getTimestamp());
                $tijdTotVolDate         = date('H:i:s', $timestampVol);
                $result                 = explode(':', $tijdTotVolDate);
                $tijdTotVol['uur']      = $result[0] - 1;
                $tijdTotVol['minuten']  = $result[1];
                $tijdTotVol['secondes'] = $result[2];
            }
        }
        $nieuwsItems = array();
        foreach ($results as $result) {
            $nieuwsItems[] = $result->getAll();
        }
        return $this->render(
            'default/nieuws.html.twig',
            array(
                'nieuwsItems'         => $nieuwsItems,
                'menuItems'           => $this->menuItems,
                'sponsors'            => $this->sponsors,
                'aantalPlekken'       => $aantalPlekken,
                'tijdVol'             => $tijdVol,
                'tijdTotVol'          => $tijdTotVol,
                'inschrijvingGeopend' => $this->isAfterOpeningInschrijving(),
            )
        );
    }

    private function getWedstrijdindelingPage()
    {
        $juryIndeling = $this->getJuryIndeling();
        $tijdSchema   = $this->getTijdSchema();
        /** @var ScoresRepository $repo */
        $repo        = $this->getDoctrine()->getRepository('AppBundle:Scores');
        $dagen       = $repo->getDagen();
        $sortedDagen = [];
        foreach ($dagen as $dag) {
            switch ($dag['wedstrijddag']) {
                case 'Donderdag':
                    $sortedDagen[0] = $dag;
                    break;
                case 'Vrijdag':
                    $sortedDagen[1] = $dag;
                    break;
                case 'Zaterdag':
                    $sortedDagen[2] = $dag;
                    break;
                case 'Zondag':
                    $sortedDagen[3] = $dag;
                    break;
                case 'Maandag':
                    $sortedDagen[4] = $dag;
                    break;
                case 'Dinsdag':
                    $sortedDagen[5] = $dag;
                    break;
                case 'Woensdag':
                    $sortedDagen[6] = $dag;
                    break;
            }
        }
        ksort($sortedDagen);
//        usort($dagen, function($a, $b) {
//            if ($a['wedstrijddag'] == $b['wedstrijddag']) {
//                return 0;
//            }
//            return ($a['wedstrijddag'] < $b['wedstrijddag']) ? -1 : 1;
//        });
        $banen           = [];
        $wedstrijdrondes = [];
        $categorieNiveau = [];
        foreach ($sortedDagen as $dag) {
            $banen[$dag['wedstrijddag']]           = $repo->getBanenPerDag($dag['wedstrijddag']);
            $wedstrijdrondes[$dag['wedstrijddag']] = $repo->getWedstrijdrondesPerDag($dag['wedstrijddag']);
            foreach ($banen[$dag['wedstrijddag']] as $baan) {
                foreach ($wedstrijdrondes[$dag['wedstrijddag']] as $wedstrijdronde) {
                    $categorieNiveau[$dag['wedstrijddag']][$wedstrijdronde['wedstrijdronde']][$baan['baan']]
                        = $repo->getNiveausPerDagPerRondePerBaan
                    (
                        $dag['wedstrijddag'],
                        $wedstrijdronde['wedstrijdronde'],
                        $baan['baan']
                    );

                }
            }
        }
        return $this->render(
            'default/wedstrijdIndeling.html.twig',
            array(
                'menuItems'       => $this->menuItems,
                'sponsors'        => $this->sponsors,
                'dagen'           => $sortedDagen,
                'banen'           => $banen,
                'wedstrijdrondes' => $wedstrijdrondes,
                'categorieNiveau' => $categorieNiveau,
                'juryIndeling'    => $juryIndeling,
                'tijdSchema'      => $tijdSchema,
            )
        );
    }

    /**
     * @Route("/inloggen/new_pass/", name="getNewPassPage", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNewPassPageAction(Request $request)
    {
        $this->setBasicPageData();
        if ($request->getMethod() == 'POST') {
            $username = $request->request->get('username');
            $user     = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->loadUserByUsername($username);
            if (!$user) {
                $this->addFlash(
                    'error',
                    'Deze gebruikersnaam bestaat niet'
                );
            } else {
                $password = $this->generatePassword();
                $encoder  = $this->container
                    ->get('security.encoder_factory')
                    ->getEncoder($user);
                $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
                $this->addToDB($user);
                $subject        = 'Inloggegevens website Haagse Donar Cup';
                $to             = $user->getEmail();
                $view           = 'mails/new_password.txt.twig';
                $mailParameters = array(
                    'username' => $user->getUsername(),
                    'password' => $password,
                );
                $this->sendEmail($subject, $to, $view, $mailParameters);
                $this->addFlash(
                    'success',
                    'Een nieuw wachtwoord is gemaild'
                );
                return $this->redirectToRoute('login_route');
            }
        }

        return $this->render(
            'security/newPass.html.twig',
            array(
                'menuItems' => $this->menuItems,
                'sponsors'  => $this->sponsors,
            )
        );
    }
}
