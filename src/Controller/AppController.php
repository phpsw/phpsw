<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController
{
    public function indexAction(Request $request, Application $app)
    {
        return new Response($app['twig']->render('index.html.twig'), 200, [
            'Cache-Control' => 's-maxage=3600'
        ]);
    }
}
