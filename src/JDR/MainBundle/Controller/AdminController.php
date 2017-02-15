<?php

namespace JDR\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
    public function adminEntryAction()
    {
        return $this->render('JDRMainBundle:Default:admin.html.twig');
    }
}