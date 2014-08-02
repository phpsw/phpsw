<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController
{
    public function indexAction(Application $app, Request $request)
    {
        return $this->render($app, 'index.html.twig');
    }

    public function brandAction(Application $app, Request $request)
    {
        return $this->render($app, 'brand.html.twig');
    }

    public function eventsAction(Application $app, Request $request)
    {
        $statuses = ['upcoming', 'past'];

        if ($app['debug']) {
            array_unshift($statuses, 'draft');
        }

        $draftEvents = $app['meetup.client']->getDraftEvents();
        $pastEvents = $app['meetup.client']->getPastEvents();
        $upcomingEvents = $app['meetup.client']->getUpcomingEvents();

        return $this->render($app, 'events.html.twig', [
            'next_event' => array_shift($upcomingEvents),
            'statuses' => $statuses,
            'draft_events' => $draftEvents,
            'past_events' => $pastEvents,
            'upcoming_events' => $upcomingEvents
        ]);
    }

    public function eventAction(Application $app, Request $request, $id)
    {
        $event = $app['meetup.client']->getEvent($id);

        if (!$event) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $this->render($app, 'event.html.twig', [
            'event' => $event
        ]);
    }

    public function speakersAction(Application $app, Request $request)
    {
        return $this->render($app, 'speakers.html.twig');
    }

    public function sponsorsAction(Application $app, Request $request)
    {
        return $this->render($app, 'sponsors.html.twig');
    }

    protected function render($app, $template, $parameters = [])
    {
        $parameters['meetup'] = $app['meetup.client'];

        return new Response(
            $app['twig']->render($template, $parameters), 200, [
                'Cache-Control' => 's-maxage=3600'
            ]
        );
    }
}
