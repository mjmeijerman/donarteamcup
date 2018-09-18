<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends BaseController
{

    /**
     * @Route("/login", name="login_route")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loginAction()
    {
        $this->setBasicPageData();
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
                'menuItems'     => $this->menuItems,
                'sponsors'      => $this->sponsors,
            )
        );
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
    }
}
