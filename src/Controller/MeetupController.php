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

        $draftEvents = $app['meetup.client']->getDraftEvents();
        $pastEvents = $app['meetup.client']->getPastEvents();
        $upcomingEvents = $app['meetup.client']->getUpcomingEvents();

        return $app['twig']->render('meetup/events.html.twig', [
            'next_event' => array_shift($upcomingEvents),
            'statuses' => $statuses,
            'draft_events' => $draftEvents,
            'past_events' => $pastEvents,
            'upcoming_events' => $upcomingEvents
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

    public function speakersAction(Request $request, Application $app)
    {
        return $app['twig']->render('meetup/speakers.html.twig', [
            'speakers' => $app['meetup.client']->getSpeakers()
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
