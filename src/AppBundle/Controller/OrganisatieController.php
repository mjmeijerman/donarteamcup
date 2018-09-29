<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Betaling;
use AppBundle\Entity\Instellingen;
use AppBundle\Entity\JuryIndeling;
use AppBundle\Entity\Jurylid;
use AppBundle\Entity\Reglementen;
use AppBundle\Entity\TijdSchema;
use AppBundle\Entity\ToegestaneNiveaus;
use AppBundle\Entity\Turnster;
use AppBundle\Entity\User;
use AppBundle\Entity\UserRepository;
use AppBundle\Entity\Voorinschrijving;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("has_role('ROLE_ORGANISATIE')")
 */
class OrganisatieController extends BaseController
{

    /**
     * @Route("/organisatie/{page}/", name="organisatieGetContent", defaults={"page" = "Mijn gegevens"}, methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function getOrganisatiePage(Request $request, $page)
    {
        $this->updateGereserveerdePlekken();
        $this->setBasicPageData('Organisatie');
        switch ($page) {
            case 'Home':
                return $this->getOrganisatieHomePage();
            case 'To-do lijst':
                return $this->getOrganisatieGegevensPage();
            case 'Instellingen':
                return $this->getOrganisatieInstellingenPage();
            case 'Mails':
                return $this->getOrganisatieGegevensPage();
            case 'Inschrijvingen':
                return $this->getOrganisatieInschrijvingenPage();
            case 'Juryzaken':
                return $this->getJuryPage($request, $page);
            case 'Financieel':
                return $this->getOrganisatieFacturenPage();
            case 'Mijn gegevens':
                return $this->getOrganisatieGegevensPage();
            case 'Vloermuziek':
                return $this->getOrganisatieVloermuziekPage();
            default:
                throw new \Exception('This is crazy');
        }
    }

    /**
     * @Route("/organisatie/removeContactpersoon/{id}/", name="removeContactpersoon", methods={"GET", "POST"})
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeContactpersoon(Request $request, $id)
    {
        $this->setBasicPageData('Organisatie');
        /** @var User $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(
                array('id' => $id)
            );
        if ($result) {
            if ($request->getMethod() == 'POST') {
                if ($request->request->get('bevestig')) {
                    $this->removeFromDB($result);
                    return $this->redirectToRoute('organisatieGetContent', ['page' => 'Inschrijvingen']);
                }
            }
            $contactpersoon = [
                'naam'       => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'vereniging' => $result->getVereniging()->getNaam() . ', ' . $result->getVereniging()->getPlaats(),
            ];
            return $this->render(
                'organisatie/removeContactpersoon.html.twig',
                array(
                    'menuItems'                       => $this->menuItems,
                    'contactpersoon'                  => $contactpersoon,
                    'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                    'totaalAantalTurnsters'           => $this->aantalTurnsters,
                    'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                    'totaalAantalJuryleden'           => $this->aantalJury,
                )
            );
        }
        return $this->redirectToRoute('organisatieGetContent', ['page' => 'Inschrijvingen']);
    }

    /**
     * @Route("/organisatie/{page}/addReglementen/", name="addReglementen", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addReglementenAction(Request $request, $page)
    {
        $this->setBasicPageData('Organisatie');
        $file = new Reglementen();
        $form = $this->createFormBuilder($file)
            ->add('naam')
            ->add('file')
            ->add('uploadBestand', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $file->setUploader($user->getUsername());
            $file->setCreatedAt(new \DateTime('now'));
            $this->addToDB($file);
            return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
        } else {
            return $this->render(
                'organisatie/reglementen.html.twig',
                array(
                    'menuItems'                       => $this->menuItems,
                    'form'                            => $form->createView(),
                    'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                    'totaalAantalTurnsters'           => $this->aantalTurnsters,
                    'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                    'totaalAantalJuryleden'           => $this->aantalJury,
                )
            );
        }
    }

    /**
     * @Route("/organisatie/{page}/addjuryIndeling/", name="addjuryIndeling", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addjuryIndelingAction(Request $request, $page)
    {
        $this->setBasicPageData('Organisatie');
        $file = new JuryIndeling();
        $form = $this->createFormBuilder($file)
            ->add('naam')
            ->add('file')
            ->add('uploadBestand', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $file->setUploader($user->getUsername());
            $file->setCreatedAt(new \DateTime('now'));
            $this->addToDB($file);
            return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
        } else {
            return $this->render(
                'organisatie/juryIndeling.html.twig',
                array(
                    'menuItems'                       => $this->menuItems,
                    'form'                            => $form->createView(),
                    'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                    'totaalAantalTurnsters'           => $this->aantalTurnsters,
                    'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                    'totaalAantalJuryleden'           => $this->aantalJury,
                )
            );
        }
    }

    /**
     * @Route("/organisatie/{page}/tijdSchema/", name="addtijdSchema", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addtijdSchemaAction(Request $request, $page)
    {
        $this->setBasicPageData('Organisatie');
        $file = new TijdSchema();
        $form = $this->createFormBuilder($file)
            ->add('naam')
            ->add('file')
            ->add('uploadBestand', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $file->setUploader($user->getUsername());
            $file->setCreatedAt(new \DateTime('now'));
            $this->addToDB($file);
            return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
        } else {
            return $this->render(
                'organisatie/tijdSchema.html.twig',
                array(
                    'menuItems'                       => $this->menuItems,
                    'form'                            => $form->createView(),
                    'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                    'totaalAantalTurnsters'           => $this->aantalTurnsters,
                    'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                    'totaalAantalJuryleden'           => $this->aantalJury,
                )
            );
        }
    }

    private function getGegevens()
    {
        $userObject = $this->getUser();
        return $userObject->getAll();
    }

    /**
     * @param Request $request
     * @param         $page
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getJuryPage(Request $request, $page)
    {
        if ($request->getMethod() === 'POST') {
            if (
                !empty($request->request->get('userId'))
                && !empty($request->request->get('juryEmail'))
                && !empty($request->request->get('juryVoornaam'))
                && !empty($request->request->get('juryAchternaam'))
                && !empty($request->request->get('brevet'))
                && !empty($request->request->get('dag'))
            ) {

                /** @var User $user */
                $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($request->request->get('userId'));

                $jurylid = new Jurylid();
                $jurylid->setEmail($request->request->get('juryEmail'));
                $jurylid->setVoornaam($request->request->get('juryVoornaam'));
                $jurylid->setAchternaam($request->request->get('juryAchternaam'));
                $jurylid->setBrevet($request->request->get('brevet'));
                switch ($request->request->get('dag')) {
                    case 'za':
                        $jurylid->setZaterdag(true);
                        $jurylid->setZondag(false);
                        break;
                    case 'zo':
                        $jurylid->setZaterdag(false);
                        $jurylid->setZondag(true);
                        break;
                    case 'zazo':
                        $jurylid->setZaterdag(true);
                        $jurylid->setZondag(true);
                        break;
                }
                $jurylid->setUser($user);

                $user->addJurylid($jurylid);

                $this->addToDB($user);

                $this->addFlash('success', 'Jurylid succesvol toegevoegd');
                return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
            } else {
                $this->addFlash('danger', 'Niet alle gegevens zijn goed ingevoerd!');
            }
        }

        /** @var User[] $users */
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->loadUsersByRole('ROLE_CONTACT');

        usort(
            $users,
            function (User $a, User $b) {
                return strcmp($a->getVereniging()->getNaam(), $b->getVereniging()->getNaam());
            }
        );

        $juryIndeling = $this->getJuryIndeling();
        /** @var Jurylid[] $results */
        $results       = $this->getDoctrine()->getRepository('AppBundle:Jurylid')
            ->getAllJuryleden();
        $juryleden     = [];
        $juryledenNiet = [];
        foreach ($results as $result) {

            if ($this->getDoctrine()->getRepository('AppBundle:Turnster')->getIngeschrevenTurnsters(
                    $result->getUser
                    ()
                ) > 0
            ) {
                $juryleden[] = [
                    'id'         => $result->getId(),
                    'naam'       => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                    'vereniging' => $result->getUser()->getVereniging()->getNaam() . ' ' . $result->getUser()
                            ->getVereniging()->getPlaats(),
                    'brevet'     => $result->getBrevet(),
                    'dag'        => $this->getBeschikbareDag($result),
                    'opmerking'  => $result->getOpmerking(),
                    'email'      => $result->getEmail(),
                ];
            } else {
                $juryledenNiet[] = [
                    'id'         => $result->getId(),
                    'naam'       => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                    'vereniging' => $result->getUser()->getVereniging()->getNaam() . ' ' . $result->getUser()
                            ->getVereniging()->getPlaats(),
                    'brevet'     => $result->getBrevet(),
                    'dag'        => $this->getBeschikbareDag($result),
                    'opmerking'  => $result->getOpmerking(),
                    'email'      => $result->getEmail(),
                ];
            }
        }
        return $this->render(
            'organisatie/organisatieJuryPage.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'juryleden'                       => $juryleden,
                'juryledenNiet'                   => $juryledenNiet,
                'juryIndeling'                    => $juryIndeling,
                'users'                           => $users
            )
        );
    }

    private function getOrganisatieGegevensPage()
    {
        $gegevens = $this->getGegevens();
        return $this->render(
            'organisatie/organisatieGegevens.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'gegevens'                        => $gegevens,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
            )
        );
    }

    private function getOrganisatieVloermuziekPage()
    {
        $vloermuziek = [];
        $categorien  = ['Jeugd 2', 'Junior', 'Senior'];
        $niveaus     = [
            'Div. 3',
            'Div. 3 Za',
            'Div. 3 Zo',
            'Div. 3 Ma',
            'Div. 4',
            'Div. 4 Za',
            'Div. 4 Zo',
            'Div. 4 Ma',
            'Div. 5',
            'Div. 5 Za',
            'Div. 5 Zo',
            'Div. 5 Ma',
        ];

        foreach ($categorien as $categorie) {
            foreach ($niveaus as $niveau) {
                /** @var Turnster[] $results */
                $results = $this->getDoctrine()->getRepository('AppBundle:Turnster')
                    ->getIngeschrevenTurnstersCatNiveau($categorie, $niveau);
                foreach ($results as $result) {
                    if (!$result->getVloermuziek()) {
                        $vloermuziek[$categorie][$niveau]['niet'][$result->getUser()->getId()][] = [
                            'wedstrijdNummer' => $result->getScores()->getWedstrijdnummer(),
                            'turnsterNaam'    => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                            'vereniging'      => $result->getUser()->getVereniging()->getNaam() . ' ' .
                                $result->getUser()->getVereniging()->getPlaats(),
                            'wedstrijdDag'    => $result->getScores()->getWedstrijddag(),
                            'wedstrijdRonde'  => $result->getScores()->getWedstrijdronde(),
                            'baan'            => $result->getScores()->getBaan(),
                            'groep'           => $result->getScores()->getGroep(),
                            'contactPersoon'  => $result->getUser()->getVoornaam() . ' ' . $result->getUser()
                                    ->getAchternaam(),
                            'mail'            => $result->getUser()->getEmail(),
                            'telNr'           => $result->getUser()->getTelefoonnummer(),
                        ];
                    } else {
                        $vloermuziek[$categorie][$niveau]['wel'][$result->getUser()->getId()][] = [
                            'wedstrijdNummer' => $result->getScores()->getWedstrijdnummer(),
                            'turnsterNaam'    => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                            'vereniging'      => $result->getUser()->getVereniging()->getNaam() . ' ' .
                                $result->getUser()->getVereniging()->getPlaats(),
                            'wedstrijdDag'    => $result->getScores()->getWedstrijddag(),
                            'wedstrijdRonde'  => $result->getScores()->getWedstrijdronde(),
                            'baan'            => $result->getScores()->getBaan(),
                            'groep'           => $result->getScores()->getGroep(),
                            'locatie'         => $result->getVloermuziek()->getWebPath(),
                        ];
                    }
                }
            }
        }

        return $this->render(
            'organisatie/vloermuziek.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'vloermuziek'                     => $vloermuziek,
            )
        );
    }

    /**
     * @Route("/organisatie/editInstellingen/{fieldName}/{data}/", name="editInstellingen", options={"expose"=true}, methods={"GET"})
     * @param $fieldName
     * @param $data
     *
     * @return JsonResponse
     */
    public function editInstellingen($fieldName, $data)
    {
        $fieldName           = str_replace('_', ' ', $fieldName);
        $returnData['error'] = null;
        $result              = $this->getOrganisatieInstellingen($fieldName);
        $returnData['data']  = $result[$fieldName];
        if ($data == 'null') {
            return new JsonResponse($returnData);
        }
        $instellingen = new Instellingen();
        switch ($fieldName) {
            case self::MAX_AANTAL_TEAMS:
                try {
                    $instellingen->setInstelling($fieldName);
                    $instellingen->setGewijzigd(new \DateTime('now'));
                    $instellingen->setAantal($data);
                    $this->addToDB($instellingen);
                    $result             = $this->getOrganisatieInstellingen($fieldName);
                    $returnData['data'] = $result[$fieldName];
                } catch (\Exception $e) {
                    $returnData['error'] = $e->getMessage();
                }
                break;
            default:
                try {
                    $instellingen->setInstelling($fieldName);
                    $instellingen->setGewijzigd(new \DateTime('now'));
                    $instellingen->setDatum(new \DateTime($data));
                    $this->addToDB($instellingen);
                    $result             = $this->getOrganisatieInstellingen($fieldName);
                    $returnData['data'] = $result[$fieldName];
                } catch (\Exception $e) {
                    $returnData['error'] = $e->getMessage();
                }
        }
        return new JsonResponse($returnData);
    }

    /**
     * @Route("/organisatie/{page}/genereerVoorinschrijving/", name="genereerVoorinschrijving", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function genereerVoorinschrijving(Request $request, $page)
    {
        if ($request->request->get('email')) {
            $this->createVoorinschrijvingToken($request->request->get('email'));
            $this->addFlash(
                'success',
                'Een voorinschrijvingslink is gemaild'
            );
            return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
        } else {
            $this->setBasicPageData('Organisatie');
            return $this->render(
                'organisatie/genereerVoorinschrijving.html.twig',
                array(
                    'menuItems'                       => $this->menuItems,
                    'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                    'totaalAantalTurnsters'           => $this->aantalTurnsters,
                    'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                    'totaalAantalJuryleden'           => $this->aantalJury,
                )
            );
        }
    }

    /**
     * @param $id
     */
    private function removeVoorinschrijving($id)
    {
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Voorinschrijving')
            ->findOneBy(
                array('id' => $id)
            );
        if ($result) {
            $this->removeFromDB($result);
        }
    }

    /**
     * @Route("/organisatie/{page}/removeVoorinschrijving/{id}", name="removeVoorinschrijving", methods={"GET"})
     * @param $page
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeVoorinschrijvingsPage($page, $id)
    {
        $this->removeVoorinschrijving($id);
        $this->addFlash(
            'success',
            'De link is verwijderd'
        );
        return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
    }

    private function refreshVoorinschrijving($id)
    {
        /** @var Voorinschrijving $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Voorinschrijving')
            ->findOneBy(
                array('id' => $id)
            );
        if ($result) {
            $this->createVoorinschrijvingToken($result->getTokenSentTo(), $result);
            $this->addFlash(
                'success',
                'Een nieuwe voorinschrijvingslink is gemaild'
            );
        }
    }

    /**
     * @Route("/organisatie/{page}/refreshVoorinschrijving/{id}", name="refreshVoorinschrijving", methods={"GET"})
     * @param $page
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function refreshVoorinschrijvingsPage($page, $id)
    {
        $this->refreshVoorinschrijving($id);
        return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
    }

    private function getVoorinschrijvingen()
    {
        /** @var Voorinschrijving[] $results */
        $results            = $this->getDoctrine()
            ->getRepository('AppBundle:Voorinschrijving')
            ->findBy(
                [],
                ['createdAt' => 'DESC']
            );
        $voorinschrijvingen = [];
        foreach ($results as $result) {
            $voorinschrijvingen[] = $result->getAll();
        }
        return $voorinschrijvingen;
    }

    private function getReglementen()
    {
        /** @var Reglementen[] $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Reglementen')
            ->findBy(
                [],
                ['id' => 'DESC']
            );
        if ($result) {
            $reglementen = $result[0]->getAll();
        } else {
            $reglementen = [
                'id'        => 0,
                'naam'      => '',
                'locatie'   => '',
                'createdAt' => '',
            ];
        }
        return $reglementen;
    }

    private function getOrganisatieInstellingenPage($successMessage = false)
    {
        $instellingen       = $this->getOrganisatieInstellingen();
        $voorinschrijvingen = $this->getVoorinschrijvingen();
        $reglementen        = $this->getReglementen();
        $toegestaneNiveaus  = $this->getToegestaneNiveaus();

        $disableRemoveInschrijvingenButton = $this->shouldRemoveInschrijvingenBeDisabled($instellingen);

        return $this->render(
            'organisatie/organisatieInstellingen.html.twig',
            array(
                'menuItems'                         => $this->menuItems,
                'instellingen'                      => $instellingen,
                'voorinschrijvingen'                => $voorinschrijvingen,
                'reglementen'                       => $reglementen,
                'successMessage'                    => $successMessage,
                'totaalAantalVerenigingen'          => $this->aantalVerenigingen,
                'totaalAantalTurnsters'             => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst'   => $this->aantalWachtlijst,
                'totaalAantalJuryleden'             => $this->aantalJury,
                'toegestaneNiveaus'                 => $toegestaneNiveaus,
                'disableRemoveInschrijvingenButton' => $disableRemoveInschrijvingenButton,
            )
        );
    }

    /**
     * @param $instellingen
     *
     * @return boolean
     */
    private function shouldRemoveInschrijvingenBeDisabled($instellingen)
    {
        $inschrijvingOpeningDateTime = new \DateTime($instellingen['Opening inschrijving']);
        $disableVanaf                = clone $inschrijvingOpeningDateTime;
        $disableVanaf->modify('-1 month');

        $nu = new \DateTime();

        if ($nu < $disableVanaf) {
            return false;
        }

        if ($nu->format('n') > '7') {
            return false;
        }

        return true;
    }

    private function getOrganisatieHomePage()
    {
        return $this->render(
            'organisatie/organisatieIndex.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
            )
        );
    }

    private function getContactpersonen()
    {
        /** @var User[] $results */
        $results         = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->loadUsersByRole('ROLE_CONTACT');
        $contactpersonen = [];
        foreach ($results as $result) {
            /** @var Turnster[] $turnsters */
            $turnsters           = $result->getTurnster();
            $turnstersGeplaatst  = [];
            $turnstersWachtlijst = [];
            foreach ($turnsters as $turnster) {
                if ($turnster->getAfgemeld()) {
                    continue;
                }
                if ($turnster->getWachtlijst()) {
                    $turnstersWachtlijst[] = $turnster;
                } else {
                    $turnstersGeplaatst[] = $turnster;
                }
            }
            $juryleden         = $result->getJurylid();
            $contactpersonen[] = [
                'id'                  => $result->getId(),
                'naam'                => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'vereniging'          => $result->getVereniging()->getNaam() . ', ' . $result->getVereniging()
                        ->getPlaats(),
                'turnstersGeplaatst'  => count($turnstersGeplaatst),
                'turnstersWachtlijst' => count($turnstersWachtlijst),
                'aantalJuryleden'     => count($juryleden),
                'email'               => $result->getEmail(),
                'username'            => $result->getUsername(),
            ];
        }
        return $contactpersonen;
    }

    /**
     * @param $groepen
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    private function getAantallenPerNiveau($groepen)
    {
        $aantallenPerNiveau               = [];
        $aantallenPerNiveau['geplaatst']  = [];
        $aantallenPerNiveau['wachtlijst'] = [];
        foreach ($groepen as $categorie => $niveaus) {
            $aantallenPerNiveau['geplaatst'][$categorie]  = [];
            $aantallenPerNiveau['wachtlijst'][$categorie] = [];
            foreach ($niveaus as $niveau) {
                $geboortejaren = $this->getGeboortejaarFromCategorie($categorie);
                if (is_array($geboortejaren)) {
                    $aantallenPerNiveau['geplaatst'][$categorie][$niveau]  = 0;
                    $aantallenPerNiveau['wachtlijst'][$categorie][$niveau] = 0;
                    foreach ($geboortejaren as $geboortejaar) {
                        $aantallenPerNiveau['geplaatst'][$categorie][$niveau]  += $this->getDoctrine()->getRepository
                        (
                            'AppBundle:Turnster'
                        )
                            ->getAantalTurnstersPerNiveau($geboortejaar, $niveau);
                        $aantallenPerNiveau['wachtlijst'][$categorie][$niveau] += $this->getDoctrine()->getRepository
                        (
                            'AppBundle:Turnster'
                        )
                            ->getAantalTurnstersWachtlijstPerNiveau($geboortejaar, $niveau);
                    }
                } else {
                    $aantallenPerNiveau['geplaatst'][$categorie][$niveau]  = $this->getDoctrine()->getRepository
                    (
                        'AppBundle:Turnster'
                    )
                        ->getAantalTurnstersPerNiveau($geboortejaren, $niveau);
                    $aantallenPerNiveau['wachtlijst'][$categorie][$niveau] = $this->getDoctrine()->getRepository
                    (
                        'AppBundle:Turnster'
                    )
                        ->getAantalTurnstersWachtlijstPerNiveau($geboortejaren, $niveau);
                }
            }
        }
        return $aantallenPerNiveau;
    }

    /**
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getOrganisatieFacturenPage()
    {
        /** @var User[] $results */
        $results           = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->loadUsersByRole('ROLE_CONTACT');
        $factuurInformatie = [];
        foreach ($results as $result) {
            $factuurNummer             = $this->getFactuurNummer($result);
            $bedragPerTurnster
                                       = self::BEDRAG_PER_TURNSTER; //todo: bedrag per turnster toevoegen aan instellingen
            $juryBoeteBedrag
                                       = self::JURY_BOETE_BEDRAG; //todo: boete bedrag jury tekort toevoegen aan instellingen
            $jurylidPerAantalTurnsters = self::AANTAL_TURNSTERS_PER_JURY; //todo: toevoegen als instelling
            $juryledenAantal           = $this->getDoctrine()
                ->getRepository('AppBundle:Jurylid')
                ->getIngeschrevenJuryleden($result);
            $turnstersAantal           = $this->getDoctrine()
                ->getRepository('AppBundle:Turnster')
                ->getIngeschrevenTurnsters($result);
            $turnstersAfgemeldAantal   = $this->getDoctrine()
                ->getRepository('AppBundle:Turnster')
                ->getAantalAfgemeldeTurnsters($result);

            $teLeverenJuryleden = ceil($turnstersAantal / $jurylidPerAantalTurnsters);
            if (($juryTekort = $teLeverenJuryleden - $juryledenAantal) < 0) {
                $juryTekort = 0;
            }
            $teBetalenBedrag = ($turnstersAantal + $turnstersAfgemeldAantal) * $bedragPerTurnster + $juryTekort *
                $juryBoeteBedrag;

            /** @var Betaling[] $betalingen */
            $betalingen    = $result->getBetaling();
            $betaaldBedrag = 0;
            if ($teBetalenBedrag == 0) {
                $voldaanClass = 'voldaan';
                $status       = 'Voldaan';
            } elseif (count($betalingen) == 0) {
                $voldaanClass = 'niet_voldaan';
                $status       = 'Niet voldaan';
            } else {
                foreach ($betalingen as $betaling) {
                    $betaaldBedrag += $betaling->getBedrag();
                }
                if ($betaaldBedrag < $teBetalenBedrag) {
                    $voldaanClass = 'bijna_voldaan';
                    $status       = 'Gedeeltelijk voldaan';
                } else {
                    $voldaanClass = 'voldaan';
                    $status       = 'Voldaan';
                }
            }

            $factuurInformatie[] = [
                'vereniging'       => $result->getVereniging()->getNaam() . ' ' . $result->getVereniging()->getPlaats(),
                'factuurNr'        => $factuurNummer,
                'bedrag'           => $teBetalenBedrag,
                'status'           => $status,
                'voldaanClass'     => $voldaanClass,
                'openstaandBedrag' => $teBetalenBedrag - $betaaldBedrag,
                'aantalTurnsters'  => $turnstersAantal,
                'aantalAfgemeld'   => $turnstersAfgemeldAantal,
                'juryTekort'       => $juryTekort,
                'userId'           => $result->getId(),
            ];
        }
        return $this->render(
            'organisatie/organisatieFinancieel.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'factuurInformatie'               => $factuurInformatie,
            )
        );
    }

    /**
     * @Route("/organisatie/{page}/niveauToevoegen/", name="niveauToevoegen", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function niveauToevoegen(Request $request, $page)
    {
        if ($request->getMethod() == "POST") {
            if ($request->request->get('categorie') && $request->request->get('niveau')) {
                $niveau = new ToegestaneNiveaus();
                $niveau->setCategorie($request->request->get('categorie'));
                $niveau->setNiveau($request->request->get('niveau'));
                $this->addToDB($niveau);
                return $this->redirectToRoute(
                    'organisatieGetContent',
                    array(
                        'page' => $page,
                    )
                );
            }
        }
        $this->setBasicPageData('Organisatie');
        return $this->render(
            'organisatie/niveauToevoegen.html.twig',
            [
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'categorien'                      => $this->getCategorien(),
            ]
        );
    }

    /**
     * @Route("/organisatie/{page}/niveauVerwijderen/{id}/",
     * name="niveauVerwijderenAjaxCall", options={"expose"=true}, methods={"GET"})
     * @param $id
     *
     * @return Response
     */
    public function niveauVerwijderenAjaxCall($id)
    {
        $result = $this->getDoctrine()->getRepository('AppBundle:ToegestaneNiveaus')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $this->removeFromDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/{page}/betalingInzien/{userId}/", name="betalingInzien", methods={"GET"})
     * @param $userId
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function betalingInzien($userId)
    {
        $this->setBasicPageData('Organisatie');
        /** @var User $result */
        $result                    = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['id' => $userId]);
        $factuurNummer             = $this->getFactuurNummer($result);
        $bedragPerTurnster         = self::BEDRAG_PER_TURNSTER; //todo: bedrag per turnster toevoegen aan instellingen
        $juryBoeteBedrag
                                   = self::JURY_BOETE_BEDRAG; //todo: boete bedrag jury tekort toevoegen aan instellingen
        $jurylidPerAantalTurnsters = self::AANTAL_TURNSTERS_PER_JURY; //todo: toevoegen als instelling
        $juryledenAantal           = $this->getDoctrine()
            ->getRepository('AppBundle:Jurylid')
            ->getIngeschrevenJuryleden($result);
        $turnstersAantal           = $this->getDoctrine()
            ->getRepository('AppBundle:Turnster')
            ->getIngeschrevenTurnsters($result);
        $turnstersAfgemeldAantal   = $this->getDoctrine()
            ->getRepository('AppBundle:Turnster')
            ->getAantalAfgemeldeTurnsters($result);

        $teLeverenJuryleden = ceil($turnstersAantal / $jurylidPerAantalTurnsters);
        if (($juryTekort = $teLeverenJuryleden - $juryledenAantal) < 0) {
            $juryTekort = 0;
        }
        $teBetalenBedrag = ($turnstersAantal + $turnstersAfgemeldAantal) * $bedragPerTurnster + $juryTekort *
            $juryBoeteBedrag;

        /** @var Betaling[] $betalingen */
        $betalingenObjecten = $result->getBetaling();
        $betaaldBedrag      = 0;
        $betalingen         = [];
        if (count($betalingenObjecten) == 0) {
            $voldaanClass = 'niet_voldaan';
            $status       = 'Niet voldaan';
        } else {
            /** @var Betaling $betaling */
            foreach ($betalingenObjecten as $betaling) {
                $betaaldBedrag += $betaling->getBedrag();
                $betalingen[]  = [
                    'id'     => $betaling->getId(),
                    'datum'  => $betaling->getDatumBetaald()->format('d-m-Y'),
                    'bedrag' => $betaling->getBedrag(),
                ];
            }
            if ($betaaldBedrag < $teBetalenBedrag) {
                $voldaanClass = 'bijna_voldaan';
                $status       = 'Gedeeltelijk voldaan';
            } else {
                $voldaanClass = 'voldaan';
                $status       = 'Voldaan';
            }
        }

        $factuurInformatie = [
            'vereniging'          => $result->getVereniging()->getNaam() . ' ' . $result->getVereniging()->getPlaats(),
            'factuurNr'           => $factuurNummer,
            'bedrag'              => $teBetalenBedrag,
            'status'              => $status,
            'voldaanClass'        => $voldaanClass,
            'openstaandBedrag'    => $teBetalenBedrag - $betaaldBedrag,
            'betaaldBedrag'       => $betaaldBedrag,
            'aantalTurnsters'     => $turnstersAantal,
            'aantalAfgemeld'      => $turnstersAfgemeldAantal,
            'juryTekort'          => $juryTekort,
            'userId'              => $result->getId(),
            'contactpersoonNaam'  => $result->getVoornaam() . ' ' . $result->getAchternaam(),
            'contactpersoonEmail' => $result->getEmail(),
            'contactpersoonTel'   => $result->getTelefoonnummer(),
        ];
        return $this->render(
            'organisatie/betalingInzien.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'factuurInformatie'               => $factuurInformatie,
                'betalingen'                      => $betalingen,
            )
        );
    }

    /**
     * @Route("/organisatie/{page}/organisatieGetFacturen/{userId}/", name="organisatieGetFacturen", methods={"GET"})
     * @param $userId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function organisatieGetFacturen($userId)
    {
        return $this->pdfFactuur($userId);
    }

    /**
     * @Route("/organisatie/{page}/removeBetaling/{userId}/", name="removeBetaling", methods={"POST"})
     * @param Request $request
     * @param         $page
     * @param         $userId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeBetaling(Request $request, $page, $userId)
    {
        /** @var Betaling $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:Betaling')
            ->findOneBy(['id' => $request->request->get('betaling')]);
        if (!$result) {
            $this->addFlash(
                'error',
                'Betaling niet gevonden'
            );
            return $this->redirectToRoute(
                'betalingInzien',
                [
                    'page'   => $page,
                    'userId' => $userId,
                ]
            );
        }
        $this->removeFromDB($result);
        $this->addFlash(
            'success',
            'Betaling succesvol verwijderd!'
        );
        return $this->redirectToRoute(
            'betalingInzien',
            [
                'page'   => $page,
                'userId' => $userId,
            ]
        );
    }

    /**
     * @Route("/organisatie/{page}/addBetaling/{userId}/", name="addBetaling", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     * @param         $userId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addBetaling(Request $request, $page, $userId)
    {
        /** @var User $result */
        $result = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['id' => $userId]);
        if ($request->getMethod() == "POST") {
            $postedToken = $request->request->get('csrfToken');
            if (!empty($postedToken)) {
                if ($this->isTokenValid($postedToken)) {
                    $betaling = new Betaling();
                    $betaling->setBedrag(str_replace(',', '.', $request->request->get('bedrag')));
                    $betaling->setDatumBetaald(new \DateTime($request->request->get('datum')));
                    $betaling->setUser($result);
                    $result->addBetaling($betaling);
                    $this->addToDB($result);
                    $this->addFlash(
                        'success',
                        'Betaling succesvol toegevoegd!'
                    );
                    return $this->redirectToRoute(
                        'betalingInzien',
                        [
                            'page'   => $page,
                            'userId' => $userId,
                        ]
                    );
                }
            }
        }
        $this->setBasicPageData('Organisatie');
        $factuurNr      = $this->getFactuurNummer($result);
        $contactpersoon = [
            'id'                  => $result->getId(),
            'contactpersoonNaam'  => $result->getVoornaam() . ' ' . $result->getAchternaam(),
            'contactpersoonEmail' => $result->getEmail(),
            'contactpersoonTel'   => $result->getTelefoonnummer(),
            'vereniging'          => $result->getVereniging()->getNaam() . ' ' . $result->getVereniging()->getPlaats(),
        ];
        $csrfToken      = $this->getToken();
        return $this->render(
            'organisatie/addBetaling.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'factuurNr'                       => $factuurNr,
                'contactpersoon'                  => $contactpersoon,
                'csrfToken'                       => $csrfToken,
            )
        );
    }

    /**
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getOrganisatieInschrijvingenPage()
    {
        $groepen            = $this->getGroepen();
        $aantallenPerNiveau = $this->getAantallenPerNiveau($groepen);
        $contactpersonen    = $this->getContactpersonen();
        return $this->render(
            'organisatie/organisatieInschrijvingen.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'contactpersonen'                 => $contactpersonen,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'groepen'                         => $groepen,
                'aantallenPerNiveau'              => $aantallenPerNiveau,
            )
        );
    }

    /**
     * @Route("/organisatie/bekijkInschrijvingenPerNiveau/removeOrganisatieTurnster/{id}",
     * name="removeOrganisatieTurnsterAjaxCall", options={"expose"=true}, methods={"GET"})
     * @param $id
     *
     * @return Response
     */
    public function removeOrganisatieTurnsterAjaxCall($id)
    {
        $result = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $this->removeFromDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/Instellingen/publiceeerUitslag/{id}",
     * name="publiceeerUitslagAjaxCall", options={"expose"=true}, methods={"GET"})
     * @param $id
     *
     * @return Response
     */
    public function publiceeerUitslagAjaxCall($id)
    {
        /** @var ToegestaneNiveaus $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:ToegestaneNiveaus')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $result->setUitslagGepubliceerd(1);
            $this->addToDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/Instellingen/annuleerPubliceren/{id}",
     * name="annuleerPublicerenAjaxCall", options={"expose"=true}, methods={"GET"})
     * @param $id
     *
     * @return Response
     */
    public function annuleerPublicerenAjaxCall($id)
    {
        /** @var ToegestaneNiveaus $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:ToegestaneNiveaus')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $result->setUitslagGepubliceerd(0);
            $this->addToDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/Juryzaken/changeJuryDagAjaxCall/{id}/{dag}/",
     * name="changeJuryDagAjaxCall", options={"expose"=true}, methods={"GET"})
     * @param $id
     * @param $dag
     *
     * @return Response
     */
    public function changeJuryDagAjaxCall($id, $dag)
    {
        /** @var Jurylid $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:Jurylid')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $result->setZaterdag(false);
            $result->setZondag(false);
            $this->setJurylidBeschikbareDagenFromPostData($dag, $result);
            $this->addToDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/bekijkInschrijvingenPerNiveau/removeOrganisatieJury/{id}",
     * name="removeOrganisatieJuryAjaxCall", options={"expose"=true}, methods={"GET"})
     * @param $id
     *
     * @return Response
     */
    public function removeOrganisatieJuryAjaxCall($id)
    {
        $result = $this->getDoctrine()->getRepository('AppBundle:Jurylid')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $this->removeFromDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/bekijkInschrijvingenPerNiveau/moveTurnsterToWachtlijst/{id}",
     * name="moveTurnsterToWachtlijst", options={"expose"=true}, methods={"GET"})
     * @param $id
     *
     * @return Response
     */
    public function moveTurnsterToWachtlijst($id)
    {
        /** @var Turnster $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $result->setWachtlijst(true);
            $this->addToDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/bekijkInschrijvingenPerNiveau/moveTurnsterFromWachtlijst/{id}",
     * name="moveTurnsterFromWachtlijst", options={"expose"=true}, methods={"GET"})
     * @param $id
     *
     * @return Response
     */
    public function moveTurnsterFromWachtlijst($id)
    {
        /** @var Turnster $result */
        $result = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->findOneBy(['id' => $id]);
        if ($result) {
            $result->setWachtlijst(false);
            $this->addToDB($result);
        }
        return new JsonResponse('true');
    }

    /**
     * @Route("/organisatie/{page}/bekijkInschrijvingenPerNiveau/{categorie}/{niveau}/", name="bekijkInschrijvingenPerNiveau", methods={"GET"})
     * @param $categorie
     * @param $niveau
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function bekijkInschrijvingenPerNiveau($categorie, $niveau)
    {
        /* todo:
         * todo: Naar wachtlijst:
         * todo: Javascript functie
         * todo: Doe ajax call, bij success: getElementById, remove element en add element to wachtlijst
         * todo: Idem van wachtlijst af
         * todo: Verwijderen ook via ajax call en remove element (get element by id)
         */
        /** @var Turnster[] $results */
        $results   = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->getIngeschrevenTurnstersCatNiveau($categorie, $niveau);
        $turnsters = [];
        foreach ($results as $result) {
            $turnsters[] = [
                'id'         => $result->getId(),
                'naam'       => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'vereniging' => $result->getUser()->getVereniging()->getNaam() . ' ' . $result->getUser()
                        ->getVereniging()->getPlaats(),
                'opmerking'  => $result->getOpmerking(),
            ];
        }
        $results    = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->getWachtlijstTurnstersCatNiveau($categorie, $niveau);
        $wachtlijst = [];
        foreach ($results as $result) {
            $wachtlijst[] = [
                'id'         => $result->getId(),
                'naam'       => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'vereniging' => $result->getUser()->getVereniging()->getNaam() . ' ' . $result->getUser()
                        ->getVereniging()->getPlaats(),
                'opmerking'  => $result->getOpmerking(),
            ];
        }
        $this->setBasicPageData('Organisatie');
        return $this->render(
            'organisatie/bekijkInschrijvingenPerNiveau.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'categorie'                       => $categorie,
                'niveau'                          => $niveau,
                'turnsters'                       => $turnsters,
                'wachtlijst'                      => $wachtlijst,
            )
        );
    }

    /**
     * @Route("/organisatie/{page}/bekijkInschrijvingenPerContactpersoon/{userId}/",
     * name="bekijkInschrijvingenPerContactpersoon", methods={"GET"})
     * @param $userId
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function bekijkInschrijvingenPerContactpersoon($userId)
    {
        /* todo:
         * todo: Naar wachtlijst:
         * todo: Javascript functie
         * todo: Doe ajax call, bij success: getElementById, remove element en add element to wachtlijst
         * todo: Idem van wachtlijst af
         * todo: Verwijderen ook via ajax call en remove element (get element by id)
         */
        $this->setBasicPageData('Organisatie');
        /** @var User $user */
        $user           = $this->getDoctrine()->getRepository("AppBundle:User")
            ->findOneBy(['id' => $userId]);
        $contactpersoon = [
            'id'             => $user->getId(),
            'vereniging'     => $user->getVereniging()->getNaam() . ' ' . $user->getVereniging()->getPlaats(),
            'gebruikersnaam' => $user->getUsername(),
            'naam'           => $user->getVoornaam() . ' ' . $user->getAchternaam(),
            'email'          => $user->getEmail(),
            'telNr'          => $user->getTelefoonnummer(),

        ];
        /** @var Turnster[] $results */
        $results   = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->getIngeschrevenTurnstersForUser($user);
        $turnsters = [];
        foreach ($results as $result) {
            if ($result->getVloermuziek()) {
                $vloermuziek = true;
                $locatie     = $result->getVloermuziek()->getWebPath();
            } else {
                $vloermuziek = false;
                $locatie     = '';
            }
            $turnsters[] = [
                'id'                 => $result->getId(),
                'naam'               => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'geboortejaar'       => $result->getGeboortejaar(),
                'categorie'          => $result->getCategorie(),
                'niveau'             => $result->getNiveau(),
                'opmerking'          => $result->getOpmerking(),
                'vloermuziek'        => $vloermuziek,
                'vloermuziekLocatie' => $locatie,

            ];
        }
        $results    = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->getWachtlijstTurnstersForUser($user);
        $wachtlijst = [];
        foreach ($results as $result) {
            $wachtlijst[] = [
                'id'           => $result->getId(),
                'naam'         => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'geboortejaar' => $result->getGeboortejaar(),
                'categorie'    => $result->getCategorie(),
                'niveau'       => $result->getNiveau(),
                'opmerking'    => $result->getOpmerking(),
            ];
        }
        $results  = $this->getDoctrine()->getRepository('AppBundle:Turnster')
            ->getAfgemeldTurnstersForUser($user);
        $afgemeld = [];
        foreach ($results as $result) {
            $afgemeld[] = [
                'id'           => $result->getId(),
                'naam'         => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'geboortejaar' => $result->getGeboortejaar(),
                'categorie'    => $result->getCategorie(),
                'niveau'       => $result->getNiveau(),
                'opmerking'    => $result->getOpmerking(),
            ];
        }
        /** @var Jurylid[] $results */
        $results   = $this->getDoctrine()->getRepository('AppBundle:Jurylid')
            ->getIngeschrevenJuryledenPerUser($user);
        $juryleden = [];
        foreach ($results as $result) {
            $juryleden[] = [
                'id'        => $result->getId(),
                'naam'      => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                'brevet'    => $result->getBrevet(),
                'dag'       => $this->getBeschikbareDag($result),
                'opmerking' => $result->getOpmerking(),
            ];
        }
        return $this->render(
            'organisatie/bekijkInschrijvingenPerContactpersoon.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
                'turnsters'                       => $turnsters,
                'wachtlijst'                      => $wachtlijst,
                'afgemeld'                        => $afgemeld,
                'juryleden'                       => $juryleden,
                'contactpersoon'                  => $contactpersoon,
            )
        );
    }

    /**
     * @Route("/organisatie/{page}/editPassword/", name="editPassword", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function editPassword(Request $request, $page)
    {
        if ($page == 'Mijn gegevens') {
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
                    return $this->redirectToRoute(
                        'organisatieGetContent',
                        array(
                            'page' => $page,
                        )
                    );
                }
            }
            $this->setBasicPageData('Organisatie');
            return $this->render(
                'organisatie/editPassword.html.twig',
                array(
                    'menuItems'                       => $this->menuItems,
                    'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                    'totaalAantalTurnsters'           => $this->aantalTurnsters,
                    'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                    'totaalAantalJuryleden'           => $this->aantalJury,
                )
            );
        }

        throw new \Exception('This is crazy');
    }

    /**
     * @Route("/organisatie/{page}/uploadWedstrijdindelingen/", name="uploadWedstrijdindelingen", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function uploadWedstrijdindelingen(Request $request, $page)
    {
        if ($request->getMethod() == 'POST') {
            if ($_FILES["userfile"]) {
                if (!empty($_FILES['userfile']['name'])) {
                    $allow[0] = "csv";
                    $extentie = strtolower(substr($_FILES['userfile']['name'], -3));
                    if ($extentie == $allow[0]) {
                        if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                            if ($_FILES['userfile']['size'] < 5000000) {
                                $localfile = $_FILES['userfile']['tmp_name'];

                                ini_set("auto_detect_line_endings", "1");
                                if (($handle = fopen($localfile, "r")) !== FALSE) {
                                    $repo = $this->getDoctrine()->getRepository('AppBundle:Turnster');
                                    while (($lineData = fgetcsv($handle, 0, ";")) !== FALSE) {
                                        /** @var Turnster $turnster */
                                        $turnster = $repo->find(trim($lineData[0]));
                                        if ($turnster) {
                                            $score = $turnster->getScores();
                                            $score->setWedstrijdnummer(trim($lineData[5]));
                                            $score->setWedstrijddag(trim($lineData[6]));
                                            $score->setWedstrijdronde(trim($lineData[7]));
                                            $score->setBaan(trim($lineData[8]));
                                            $score->setGroep(trim($lineData[9]));
                                            $this->addToDB($score);
                                        }
                                    }
                                    fclose($handle);
                                }

                                return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
                            } else {
                                $this->addFlash(
                                    'error',
                                    'Helaas, de upload is mislukt: het bestand is te groot.'
                                );
                            }
                        } else {
                            $this->addFlash(
                                'error',
                                'Helaas, de upload is mislukt.'
                            );
                        }
                    } else {
                        $this->addFlash(
                            'error',
                            'Helaas, de upload is mislukt: het bestand moet .csv zijn.'
                        );
                    }
                } else {
                    $this->addFlash(
                        'error',
                        'Selecteer een bestand.'
                    );
                }
            }
        }
        $this->setBasicPageData('Organisatie');
        return $this->render(
            'organisatie/uploadWedstrijdindelingen.html.twig',
            array(
                'menuItems'                       => $this->menuItems,
                'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                'totaalAantalTurnsters'           => $this->aantalTurnsters,
                'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                'totaalAantalJuryleden'           => $this->aantalJury,
            )
        );
    }

    /**
     * @Route("/organisatie/{page}/removeInschrijvingen/", name="removeInschrijvingen", methods={"GET", "POST"})
     * @param Request $request
     * @param         $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeInschrijvingen(Request $request, $page)
    {
        if (!$this->shouldRemoveInschrijvingenBeDisabled($this->getOrganisatieInstellingen())) {
            if ($request->getMethod() == 'POST') {
                if ($request->get('confirmRemoveInschrijvingen') === 'JAZEKER') {

                    /** @var UserRepository $repository */
                    $repository = $this->getDoctrine()->getRepository('AppBundle:User');

                    /** @var User $users */
                    $users = $repository->loadUsersByRole('ROLE_CONTACT');

                    foreach ($users as $user) {
                        $this->removeFromDB($user);
                    }

                    /** @var JuryIndeling[] $result */
                    $juryIndelingen = $this->getDoctrine()
                        ->getRepository('AppBundle:JuryIndeling')
                        ->findBy(
                            [],
                            ['id' => 'DESC']
                        );

                    foreach ($juryIndelingen as $juryIndeling) {
                        $this->removeFromDB($juryIndeling);
                    }

                    /** @var TijdSchema[] $tijdschemas */
                    $tijdschemas = $this->getDoctrine()
                        ->getRepository('AppBundle:TijdSchema')
                        ->findBy(
                            [],
                            ['id' => 'DESC']
                        );

                    foreach ($tijdschemas as $tijdschema) {
                        $this->removeFromDB($tijdschema);
                    }

                    $this->rrmdir('uploads/vloermuziek');

                    return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
                }
            }

            $this->setBasicPageData('Organisatie');
            return $this->render(
                'organisatie/removeInschrijvingen.html.twig',
                array(
                    'menuItems'                       => $this->menuItems,
                    'totaalAantalVerenigingen'        => $this->aantalVerenigingen,
                    'totaalAantalTurnsters'           => $this->aantalTurnsters,
                    'totaalAantalTurnstersWachtlijst' => $this->aantalWachtlijst,
                    'totaalAantalJuryleden'           => $this->aantalJury,
                )
            );
        }

        return $this->redirectToRoute('organisatieGetContent', ['page' => $page]);
    }

    private function rrmdir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
