<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\RedirectResponse;

class EventController extends AbstractController
{
    public function indexAction(Application $app)
    {
        $drafts   = $app['meetup.client']->getDraftEvents();
        $past     = $app['meetup.client']->getPastEvents();
        $upcoming = $app['meetup.client']->getUpcomingEvents();

        return $this->render($app, 'events.html.twig', [
            'event'    => array_shift($upcoming) ?: array_shift($past),
            'statuses' => array_merge(($app['debug'] ? ['draft'] : []), ['upcoming', 'past']),
            'draft'    => $drafts,
            'past'     => $past,
            'upcoming' => $upcoming
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

            if ($date == '1970-01') continue;

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
