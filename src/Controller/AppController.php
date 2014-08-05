<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response;

class AppController
{
    public function indexAction(Application $app, Request $request)
    {
        $events = $app['meetup.client']->getUpcomingEvents();

        return $this->render($app, 'index.html.twig', [
            'events' => $events
        ]);
    }

    public function brandAction(Application $app, Request $request)
    {
        return $this->render($app, 'brand.html.twig');
    }

    public function invoiceAction(Application $app, Request $request, $id)
    {
        return $this->render($app, 'invoice.html.twig', [
            'id' => $id,
            'amount' => $request->get('amount'),
            'sponsor' => $request->get('sponsor')
        ]);
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

    public function eventAction(Application $app, Request $request, $id, $slug)
    {
        $event = $app['meetup.client']->getEvent($id);

        if (!$event) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        } elseif ($slug != $event->slug) {
            $response = new RedirectResponse(
                $app->path('event', ['id' => $id, 'slug' => $event->slug]),
                301
            );
        } else {
            $response = $this->render($app, 'event.html.twig', [
                'event' => $event
            ]);
        }

        return $response;
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
