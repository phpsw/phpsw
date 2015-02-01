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

        $events = $app['meetup.client']->getEvents();
        $draftEvents = $app['meetup.client']->getDraftEvents();
        $pastEvents = $app['meetup.client']->getPastEvents();
        $upcomingEvents = $app['meetup.client']->getUpcomingEvents();

        return $this->render($app, 'events.html.twig', [
            'next_event' => array_shift($upcomingEvents),
            'statuses' => $statuses,
            'events' => $events,
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
            $segments = array_map('trim', explode('<hr>', $event->description));

            $event->abstract = array_shift($segments);

            $event->details = implode('<hr>', array_filter($segments, function ($segment) {
                return !preg_match('#^<p><b>([^<]+)</b></p>#', $segment, $matches);
            }));

            $event->extras = array_map(
                function ($segment) {
                    preg_match('#^<p><b>([^<]+)</b></p>(.*)#', $segment, $matches);

                    list($segment, $heading, $content) = $matches;

                    return (object) [
                        'heading' => $heading,
                        'content' => $content
                    ];
                },
                array_filter($segments, function ($segment) {
                    return !!preg_match('#^<p><b>([^<]+)</b></p>#', $segment, $matches);
                })
            );

            $response = $this->render($app, 'event.html.twig', [
                'event' => $event
            ]);
        }

        return $response;
    }

    public function statsAction(Application $app)
    {
        $events = $app['meetup.client']->getEvents();
        $members = $app['meetup.client']->getMembers();

        $totals = [];

        foreach ($members as $member) {
            $date = $member->joined->format('Y-m');

            if (array_key_exists($date, $totals)) {
                $totals[$date]++;
            } else {
                $totals[$date] = 1;
            }
        }

        ksort($totals);

        $dates = array_keys($totals);

        $begin = new \DateTime(current($dates));
        $end   = new \DateTime(end($dates));

        $begin->setTime(0,0); $end->setTime(12,0);

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);

        $dates = [];
        $total = 0;

        foreach ($period as $date) {
            $date = $date->format('Y-m');

            if (array_key_exists($date, $totals)) {
                $count = $totals[$date];
            } else {
                $count = 0;
            }

            $dates[$date] = $total += $count;
        }

        return $this->render($app, 'stats.html.twig', [
            'events' => $events,
            'members' => $dates
        ]);
    }
}
