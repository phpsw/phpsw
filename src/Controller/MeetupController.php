<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request;

class MeetupController
{
    public function eventsAction(Request $request, Application $app)
    {
        $statuses = ['upcoming', 'past'];

        if ($app['debug']) {
            array_unshift($statuses, 'draft');
        }

        return $app['twig']->render('meetup/events.html.twig', [
            'statuses' => $statuses,
            'draft_events' => $app['meetup.client']->getDraftEvents(),
            'past_events' => $app['meetup.client']->getPastEvents(),
            'upcoming_events' => $app['meetup.client']->getUpcomingEvents()
        ]);
    }

    public function postsAction(Request $request, Application $app)
    {
        return $app['twig']->render('meetup/posts.html.twig', [
            'posts' => $app['meetup.client']->getPosts()
        ]);
    }

    public function photosAction(Request $request, Application $app)
    {
        $group = $app['meetup.client']->getGroup();

        return $app['twig']->render('meetup/photos.html.twig', [
            'photos' => $group ? $group->photos : []
        ]);
    }

    public function reviewsAction(Request $request, Application $app)
    {
        return $app['twig']->render('meetup/reviews.html.twig', [
            'reviews' => $app['meetup.client']->getReviews()
        ]);
    }

    public function sponsorsAction(Request $request, Application $app)
    {
        $group = $app['meetup.client']->getGroup();

        return $app['twig']->render('meetup/sponsors.html.twig', [
            'sponsors' => $group ? $group->sponsors : []
        ]);
    }
}
