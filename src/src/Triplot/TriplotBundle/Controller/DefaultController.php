<?php

namespace Triplot\TriplotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TriplotTriplotBundle:Default:index.html.twig', array('name' => $name));
    }
}
