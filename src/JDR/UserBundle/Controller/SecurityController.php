<?php

namespace JDR\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
    	// Si le visiteur est déjà identifié, on le redirige vers l'accueil
	    if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
	    	return $this->redirectToRoute('jdr_main_homepage');
	    }

    	$authenticationUtils = $this->get('security.authentication_utils');
        return $this->render('JDRUserBundle:Default:login.html.twig', array(
	      'last_email' => $authenticationUtils->getLastUsername(),
	      'error'         => $authenticationUtils->getLastAuthenticationError(),
	    ));
    }
}