<?php

namespace PHPSW\Controller;

use DMS\Service\Meetup\MeetupKeyAuthClient,
    PHPSW\API\Meetup,
    Silex\Application,
    Symfony\Component\HttpFoundation\Request;

class MeetupController
{
    public function eventsAction(Request $request, Application $app)
    {
        $meetup = new Meetup($app['meetup']);

        return $app['twig']->render('meetup/events.html.twig', [
            'events' => $meetup->getEvents(),
            'past_events' => $meetup->getPastEvents(),
            'upcoming_events' => $meetup->getUpcomingEvents()
        ]);
    }

    public function postsAction(Request $request, Application $app)
    {
        $meetup = new Meetup($app['meetup']);

        return $app['twig']->render('meetup/posts.html.twig', [
            'posts' => array_slice($meetup->getPosts(), 0, 3)
        ]);
    }

    public function sponsorsAction(Request $request, Application $app)
    {
        $meetup = new Meetup($app['meetup']);

        $group = $meetup->getGroup();

        return $app['twig']->render('meetup/sponsors.html.twig', [
            'sponsors' => $group ? $group->sponsors : []
        ]);
    }
}
