<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request;

class SpeakerController extends AbstractController
{
    public function indexAction(Application $app)
    {
        return $this->render($app, 'speakers.html.twig', [
            'speakers' => $app['meetup.client']->getSpeakers()
        ]);
    }

    public function showAction(Application $app, $slug)
    {
        $speaker = $app['meetup.client']->getSpeaker($slug);

        if (!$speaker) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $this->render($app, 'speaker.html.twig', [
            'speaker' => $speaker
        ]);
    }
}
