<?php

namespace PHPSW\Controller;

use PHPSW\API\Meetup,
    Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController
{
    public function indexAction(Request $request, Application $app)
    {
        $meetup = new Meetup($app['meetup']);

        return new Response($app['twig']->render('index.html.twig', ['meetup' => $meetup]), 200, [
            'Cache-Control' => 's-maxage=3600'
        ]);
    }
}
