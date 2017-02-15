<?php

namespace JDR\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('JDRMainBundle:Default:index.html.twig');
    }
}
