<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController
{
    public function indexAction(Application $app, Request $request)
    {
        $communities = [
            (object) [
                'name' => 'Bristol Web Folk',
                'logo' => 'bristol-web-folk',
                'url'  => 'http://bristolwebfolk.github.io'
            ],
            (object) [
                'name' => 'Bristol Drupal Group',
                'logo' => 'drupal-bristol',
                'url'  => 'https://groups.drupal.org/bristol-and-west-uk'
            ],
            (object) [
                'name' => 'BrightonPHP',
                'logo' => 'brighton-php',
                'url'  => 'http://brightonphp.org'
            ],
            (object) [
                'name' => 'PHP Dorset',
                'logo' => 'php-dorset',
                'url'  => 'http://www.phpdorset.co.uk'
            ],
            (object) [
                'name' => 'PHP London',
                'logo' => 'php-london',
                'url'  => 'http://phplondon.org'
            ],
            (object) [
                'name' => 'PHP Hampshire',
                'logo' => 'php-hampshire',
                'url'  => 'http://phphants.co.uk'
            ],
            (object) [
                'name' => 'PHP Women : An inclusive &amp; global support network',
                'logo' => 'php-women',
                'url'  => 'http://phpwomen.org'
            ],
            (object) [
                'name' => 'PHP Cambridge',
                'logo' => 'php-cambridge',
                'url'  => 'http://www.meetup.com/phpcambridge'
            ],
            (object) [
                'name' => 'Leeds PHP',
                'logo' => 'leed-php',
                'url'  => 'http://leedsphp.org'
            ],
            (object) [
                'name' => 'PHP East Midlands',
                'logo' => 'phpem',
                'url'  => 'http://phpem.info'
            ],
            (object) [
                'name' => 'PHP North East',
                'logo' => 'phpne',
                'url'  => 'http://phpne.org.uk'
            ],
            (object) [
                'name' => 'PHP North West',
                'logo' => 'phpnw',
                'url'  => 'http://conference.phpnw.org.uk'
            ],
            (object) [
                'name' => 'PHP West Midlands',
                'logo' => 'phpwm',
                'url'  => 'http://phpwm.org.uk'
            ],
            (object) [
                'name' => 'PHP UK',
                'logo' => 'php-uk',
                'url'  => 'http://phpconference.co.uk'
            ]
        ];

        $body = $app['twig']->render('index.html.twig', [
            'communities' => $communities,
            'meetup' => $app['meetup.client']
        ]);

        return new Response($body, 200, ['Cache-Control' => 's-maxage=3600']);
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
                    'meetup' => $app['meetup.client'],
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
