<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController
{
    public function indexAction(Application $app, Request $request)
    {
        return new Response(
            $app['twig']->render('index.html.twig', [
                'meetup' => $app['meetup.client']
            ]),
            200,
            ['Cache-Control' => 's-maxage=3600']
        );
    }

    public function brandAction(Application $app, Request $request)
    {
        return new Response(
            $app['twig']->render('brand.html.twig'),
            200,
            ['Cache-Control' => 's-maxage=3600']
        );
    }

    public function eventsAction(Application $app, Request $request)
    {
        return new Response(
            $app['twig']->render('events.html.twig', [
                'meetup' => $app['meetup.client']
            ]),
            200,
            ['Cache-Control' => 's-maxage=3600']
        );
    }

    public function eventAction(Application $app, Request $request, $id)
    {
        $event = $app['meetup.client']->getEvent($id);

        if ($event) {
            $response = new Response(
                $app['twig']->render('event.html.twig', [
                    'event' => $event
                ]),
                200,
                ['Cache-Control' => 's-maxage=3600']
            );
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $response;
    }

    public function speakersAction(Application $app, Request $request)
    {
        return new Response(
            $app['twig']->render('speakers.html.twig', [
                'meetup' => $app['meetup.client']
            ]),
            200,
            ['Cache-Control' => 's-maxage=3600']
        );
    }

    public function sponsorsAction(Application $app, Request $request)
    {
        return new Response(
            $app['twig']->render('sponsors.html.twig', [
                'meetup' => $app['meetup.client']
            ]),
            200,
            ['Cache-Control' => 's-maxage=3600']
        );
    }
}
