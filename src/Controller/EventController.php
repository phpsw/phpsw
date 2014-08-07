<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\RedirectResponse;

class EventController extends AbstractController
{
    public function indexAction(Application $app)
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

    public function showAction(Application $app, $id, $slug)
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
}
