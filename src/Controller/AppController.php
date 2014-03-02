<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request;

class AppController
{
    public function indexAction(Request $request, Application $app)
    {
        return $app['twig']->render('index.html.twig');
    }
}
