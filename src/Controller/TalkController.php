<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\RedirectResponse;

class TalkController extends AbstractController
{
    public function indexAction(Application $app)
    {
        $events = $app['meetup.client']->getEvents();

        $talks = [];

        foreach ($events as $event) {
            foreach($event->talks as $talk) {
                if ($talk->slides || $talk->video) {
                    $talks[] = $talk;
                    $talk->event = $event;
                }
            }
        }

        return $this->render($app, 'talks.html.twig', [
            'talks' => $talks
        ]);
    }

    public function showAction(Application $app, $slug)
    {
        $talk = $app['meetup.client']->getTalk($slug);

        return $this->render($app, 'talk.html.twig', [
            'talk' => $talk
        ]);
    }
}
