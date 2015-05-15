<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\RedirectResponse;

class TalkController extends AbstractController
{
    public function indexAction(Application $app)
    {
        $events = $app['meetup.client']->getEvents();

        $featured = [];

        while (count($featured) < 6 && list($i, $event) = each($events)) {
            while (count($featured) < 6 && list($i, $talk) = each($event->talks)) {
                if ($talk->video && $app['redis']->sismember('featured', $talk->id)) {
                    $featured[] = $talk;
                    $talk->event = $event;
                }
            }
        }

        $talks = [];

        foreach ($events as $event) {
            foreach ($event->talks as $talk) {
                if (($talk->slides || $talk->video) && !in_array($talk, $featured)) {
                    $talks[] = $talk;
                    $talk->event = $event;
                }
            }
        }

        return $this->render($app, 'talks.html.twig', [
            'featured' => $featured,
            'talks'    => $talks
        ]);
    }

    public function showAction(Application $app, $slug)
    {
        $talk = $app['meetup.client']->getTalk($slug);

        if (!$talk) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $this->render($app, 'talk.html.twig', [
            'talk' => $talk
        ]);
    }
}
