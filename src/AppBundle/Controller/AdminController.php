<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Content;
use AppBundle\Entity\FileUpload;
use AppBundle\Entity\FotoUpload;
use AppBundle\Entity\Nieuwsbericht;
use AppBundle\Entity\Sponsor;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ContentType;
use AppBundle\Form\Type\EditSponsorType;
use AppBundle\Form\Type\JuryGebruikerType;
use AppBundle\Form\Type\NieuwsberichtType;
use AppBundle\Form\Type\OrganisatieType;
use AppBundle\Form\Type\SponsorType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends BaseController
{
    /**
     * @Route("/admin/", name="getAdminIndexPage", methods={"GET"})
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getIndexPageAction()
    {
        $this->setBasicPageData();
        $fotoUploads      = $this->getUploads('Foto');
        $fileUploads      = $this->getUploads('File');
        $organisatieLeden = $this->getOrganisatieLeden();
        $juryGebruikers   = $this->getJuryGebruikers();
        return $this->render(
            'admin/adminIndex.html.twig',
            array(
                'menuItems'        => $this->menuItems,
                'sponsors'         => $this->sponsors,
                'fotoUploads'      => $fotoUploads,
                'fileUploads'      => $fileUploads,
                'organisatieLeden' => $organisatieLeden,
                'juryGebruikers'   => $juryGebruikers,
            )
        );
    }

    /**
     * @return User[]
     */
    private function getOrganisatieLeden()
    {
        $results          = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->loadUsersByRole('ROLE_ORGANISATIE');
        $organisatieLeden = array();
        foreach ($results as $result) {
            $organisatieLeden[] = $result->getAll();
        }
        return $organisatieLeden;
    }

    private function getJuryGebruikers()
    {
        $results          = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->loadUsersByRole('ROLE_JURY');
        $organisatieLeden = array();
        foreach ($results as $result) {
            $organisatieLeden[] = $result->getAll();
        }
        return $organisatieLeden;
    }

    private function getUploads($type)
    {
        $results = $this->getDoctrine()
            ->getRepository('AppBundle:' . $type . 'Upload')
            ->findBy(
                array(),
                array('naam' => 'ASC')
            );
        $uploads = array();
        foreach ($results as $result) {
            $uploads[] = $result->getAll();
        }
        return $uploads;
    }

    /**
     * @Route("/admin/organisatie/add/", name="addOrganisatieLid", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addOrganisatieLid(Request $request)
    {
        $this->setBasicPageData();
        $organisatieLid = new User();
        $form           = $this->createForm(OrganisatieType::class, $organisatieLid);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->container->getParameter('standaard_wachtwoord');
            $encoder  = $this->container
                ->get('security.encoder_factory')
                ->getEncoder($organisatieLid);
            $organisatieLid->setRole('ROLE_ORGANISATIE')
                ->setIsActive(true)
                ->setCreatedAt(new \DateTime('now'))
                ->setPassword($encoder->encodePassword($password, $organisatieLid->getSalt()));
            $this->addToDB($organisatieLid);

            $subject        = 'Inloggegevens website Donar Team Cup';
            $to             = $organisatieLid->getEmail();
            $view           = 'mails/new_user.txt.twig';
            $mailParameters = array(
                'voornaam' => $organisatieLid->getVoornaam(),
                'username' => $organisatieLid->getUsername(),
                'password' => $password
            );
            $from           = 'info@donarteamcup.nl';
            $this->sendEmail($subject, $to, $view, $mailParameters, $from);

            return $this->redirectToRoute('getAdminIndexPage');
        } else {
            return $this->render(
                'admin/addOrganisatieLid.html.twig',
                array(
                    'menuItems' => $this->menuItems,
                    'form'      => $form->createView(),
                    'sponsors'  => $this->sponsors,
                )
            );
        }

    }

    /**
     * @Route("/admin/organisatie/addJuryGebruiker/", name="addJuryGebruiker", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addJuryGebruiker(Request $request)
    {
        $this->setBasicPageData();
        $organisatieLid = new User();
        $form           = $this->createForm(JuryGebruikerType::class, $organisatieLid);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $request->request->get('password');
            $encoder  = $this->container
                ->get('security.encoder_factory')
                ->getEncoder($organisatieLid);
            $organisatieLid->setRole('ROLE_JURY')
                ->setIsActive(true)
                ->setCreatedAt(new \DateTime('now'))
                ->setPassword($encoder->encodePassword($password, $organisatieLid->getSalt()))
                ->setVoornaam(' ')
                ->setAchternaam(' ')
                ->setEmail(' ');
            $this->addToDB($organisatieLid);

            return $this->redirectToRoute('getAdminIndexPage');
        } else {
            return $this->render(
                'admin/addJuryGebruiker.html.twig',
                array(
                    'menuItems' => $this->menuItems,
                    'form'      => $form->createView(),
                    'sponsors'  => $this->sponsors,
                )
            );
        }

    }

    /**
     * @Route("/admin/organisatie/edit/{username}/", name="editOrganisatieLid", methods={"GET", "POST"})
     * @param         $username
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editOrganisatieLid($username, Request $request)
    {
        $this->setBasicPageData();
        $organisatieLid = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->loadUserByUsername($username);
        $form           = $this->createForm(OrganisatieType::class, $organisatieLid);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addToDB($organisatieLid);

            return $this->redirectToRoute('getAdminIndexPage');
        } else {
            return $this->render(
                'admin/addOrganisatieLid.html.twig',
                array(
                    'menuItems' => $this->menuItems,
                    'form'      => $form->createView(),
                    'sponsors'  => $this->sponsors,
                )
            );
        }
    }

    /**
     * @Route("/admin/organisatie/remove/{username}/", name="removeOrganisatieLid", methods={"GET", "POST"})
     * @param         $username
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeOrganisatieLid($username, Request $request)
    {
        $this->setBasicPageData();
        $organisatieLid = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->loadUserByUsername($username);

        if ($request->getMethod() == 'POST') {
            $this->removeFromDB($organisatieLid);

            return $this->redirectToRoute('getAdminIndexPage');
        } else {
            $organisatieLid = $organisatieLid->getAll();
            return $this->render(
                'admin/removeOrganisatieLid.html.twig',
                array(
                    'menuItems'      => $this->menuItems,
                    'sponsors'       => $this->sponsors,
                    'organisatieLid' => $organisatieLid,
                )
            );
        }
    }

    /**
     * @Route("/admin/file/remove/{id}/{type}/", name="removeAdminFile", methods={"GET", "POST"})
     * @param         $id
     * @param Request $request
     * @param         $type
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function removeAdminFile($id, Request $request, $type)
    {
        $file = $this->getDoctrine()
            ->getRepository('AppBundle:' . $type . 'Upload')
            ->find($id);
        $this->setBasicPageData();
        if ($file) {
            if ($request->getMethod() == 'GET') {
                return $this->render(
                    'admin/removeAdminFile.html.twig',
                    array(
                        'menuItems' => $this->menuItems,
                        'sponsors'  => $this->sponsors,
                        'content'   => $file->getAll(),
                    )
                );
            } elseif ($request->getMethod() == 'POST') {
                $this->removeFromDB($file);
                return $this->redirectToRoute('getAdminIndexPage');
            } else {
                throw new \Exception('This is crazy');
            }
        }

        return $this->render(
            'error/pageNotFound.html.twig',
            array(
                'menuItems' => $this->menuItems,
                'sponsors'  => $this->sponsors,
            )
        );
    }

    /**
     * @param $type
     *
     * @return FileUpload|FotoUpload
     * @throws \Exception
     */
    private function getNewFileObject($type)
    {
        switch ($type) {
            case 'File':
                return new FileUpload();
            case 'Foto':
                return new FotoUpload();
            default:
                throw new \Exception('This is crazy');
        }
    }


    /**
     * @Route("/admin/file/add/{type}/", name="addAdminFile", methods={"GET", "POST"})
     * @param Request $request
     * @param         $type
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function addAdminFileAction(Request $request, $type)
    {
        $this->setBasicPageData();
        $file = $this->getNewFileObject($type);
        $form = $this->createFormBuilder($file)
            ->add('naam')
            ->add('file')
            ->add('uploadBestand', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addToDB($file);
            if ($type == 'Foto') {
                $this->get('helper.imageresizer')->resizeImage(
                    $file->getAbsolutePath(),
                    $file->getUploadRootDir() . "/",
                    null,
                    $width = 597
                );
            }
            return $this->redirectToRoute('getAdminIndexPage');
        } else {
            return $this->render(
                'admin/addAdminFile.html.twig',
                array(
                    'menuItems' => $this->menuItems,
                    'sponsors'  => $this->sponsors,
                    'form'      => $form->createView(),
                )
            );
        }
    }

    /**
     * @Route("/pagina/{page}/edit/", defaults={"page" = "geschiedenis"}, name="editDefaultPage", methods={"GET", "POST"})
     * @param         $page
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editDefaultPageAction($page, Request $request)
    {
        $this->setBasicPageData();
        if ($this->checkIfPageExists($page)) {
            $result = $this->getDoctrine()
                ->getRepository('AppBundle:Content')
                ->findOneBy(
                    array('pagina' => $page),
                    array('gewijzigd' => 'DESC')
                );
            $result ? $content = $result : $content = new Content();
            $form = $this->createForm(ContentType::class, $content);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $editedContent = new Content();
                $editedContent->setGewijzigd(new \DateTime('NOW'))
                    ->setPagina($page)
                    ->setContent($content->getContent());
                $this->addToDB($editedContent, $content);
                return $this->redirectToRoute('getContent', array('page' => $page));
            } else {
                return $this->render(
                    'default/editIndex.html.twig',
                    array(
                        'content'   => $content->getContent(),
                        'menuItems' => $this->menuItems,
                        'form'      => $form->createView(),
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

    /**
     * @Route("/pagina/Laatste%20nieuws/add/", name="addNieuwsPage", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addNieuwsPage(Request $request)
    {
        $this->setBasicPageData();
        $nieuwsbericht = new Nieuwsbericht();
        $form          = $this->createForm(NieuwsberichtType::class, $nieuwsbericht);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nieuwsbericht->setDatumtijd(date('d-m-Y: H:i', time()))
                ->setJaar(date('Y', time()))
                ->setBericht(str_replace("\n", "<br />", $nieuwsbericht->getBericht()));
            $this->addToDB($nieuwsbericht);
            return $this->redirectToRoute('getContent', array('page' => 'Laatste nieuws'));
        } else {
            return $this->render(
                'default/addNieuwsbericht.html.twig',
                array(
                    'form'      => $form->createView(),
                    'menuItems' => $this->menuItems,
                    'sponsors'  => $this->sponsors,
                )
            );
        }
    }

    /**
     * @Route("/pagina/Laatste%20nieuws/edit/{id}/", name="editNieuwsberichtPage", methods={"GET", "POST"})
     * @param         $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editNieuwsberichtPage($id, Request $request)
    {
        $this->setBasicPageData();
        $nieuwsbericht = $this->getDoctrine()
            ->getRepository('AppBundle:Nieuwsbericht')
            ->find($id);
        if ($nieuwsbericht) {
            $nieuwsbericht->setBericht(str_replace("<br />", "\n", $nieuwsbericht->getBericht()));
            $form = $this->createForm(NieuwsberichtType::class, $nieuwsbericht);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $nieuwsbericht->setBericht(str_replace("\n", "<br />", $nieuwsbericht->getBericht()));
                $this->addToDB($nieuwsbericht);
                return $this->redirectToRoute('getContent', array('page' => 'Laatste nieuws'));
            } else {
                return $this->render(
                    'default/addNieuwsbericht.html.twig',
                    array(
                        'form'      => $form->createView(),
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

    /**
     * @Route("/pagina/Laatste%20nieuws//remove/{id}/", name="removeNieuwsberichtPage", methods={"GET", "POST"})
     * @param         $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeNieuwsberichtPage($id, Request $request)
    {
        $this->setBasicPageData();
        $nieuwsbericht = $this->getDoctrine()
            ->getRepository('AppBundle:Nieuwsbericht')
            ->find($id);
        if ($nieuwsbericht) {
            if ($request->getMethod() == 'GET') {
                return $this->render(
                    'default/removeNieuwsbericht.html.twig',
                    array(
                        'content'   => $nieuwsbericht->getAll(),
                        'menuItems' => $this->menuItems,
                        'sponsors'  => $this->sponsors,
                    )
                );
            } else {
                $this->removeFromDB($nieuwsbericht);
                return $this->redirectToRoute('getContent', array('page' => 'Laatste nieuws'));
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

    /**
     * @Route("/pagina/Sponsors/add/", name="addSponsorPage", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addSponsorPageAction(Request $request)
    {
        $this->setBasicPageData();
        $sponsor = new Sponsor();
        $form    = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addToDB($sponsor);
            $this->get('helper.imageresizer')->resizeImage(
                $sponsor->getAbsolutePath(),
                $sponsor->getUploadRootDir() . "/",
                null,
                $width = 597
            );
            $this->get('helper.imageresizer')->resizeImage(
                $sponsor->getAbsolutePath2(),
                $sponsor->getUploadRootDir() . "/",
                null,
                $width = 597
            );
            return $this->redirectToRoute('getContent', array('page' => 'Sponsors'));
        } else {
            return $this->render(
                'default/addSponsor.html.twig',
                array(
                    'form'      => $form->createView(),
                    'menuItems' => $this->menuItems,
                    'sponsors'  => $this->sponsors,
                )
            );
        }
    }

    /**
     * @Route("/pagina/Sponsors/edit/{id}/", name="editSponsorPage", methods={"GET", "POST"})
     * @param         $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editSponsorPage($id, Request $request)
    {
        $this->setBasicPageData();
        $sponsor = $this->getDoctrine()
            ->getRepository('AppBundle:Sponsor')
            ->find($id);
        if ($sponsor) {
            $form = $this->createForm(EditSponsorType::class, $sponsor);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->addToDB($sponsor);
                return $this->redirectToRoute('getContent', array('page' => 'Sponsors'));
            } else {
                return $this->render(
                    'default/addSponsor.html.twig',
                    array(
                        'form'      => $form->createView(),
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

    /**
     * @Route("/pagina/Sponsors/remove/{id}/", name="removeSponsorPage", methods={"GET", "POST"})
     * @param         $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeSponsorPage($id, Request $request)
    {
        $this->setBasicPageData();
        /** @var EntityManager $em */
        $em    = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT sponsor
                FROM AppBundle:Sponsor sponsor
                WHERE sponsor.id = :id'
        )
            ->setParameter('id', $id);
        /** @var Sponsor $sponsor */
        $sponsor = $query->setMaxResults(1)->getOneOrNullResult();
        if ($sponsor) {
            if ($request->getMethod() == 'GET') {
                return $this->render(
                    'default/removeSponsor.html.twig',
                    array(
                        'content'   => $sponsor->getAll(),
                        'menuItems' => $this->menuItems,
                        'sponsors'  => $this->sponsors,
                    )
                );
            } else {
                $this->removeFromDB($sponsor);
                $em->remove($sponsor);
                $em->flush();
                return $this->redirectToRoute('getContent', array('page' => 'Sponsors'));
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
}
