<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController extends AbstractController
{
    public function indexAction(Application $app)
    {
        $events = $app['meetup.client']->getUpcomingEvents();

        return $this->render($app, 'index.html.twig', [
            'events' => $events
        ]);
    }

    public function brandAction(Application $app)
    {
        return $this->render($app, 'brand.html.twig');
    }

    public function invoiceAction(Application $app, Request $request)
    {
        $sponsor = $request->get('sponsor');

        return $this->render($app, 'invoice.html.twig', [
            'amount' => $request->get('amount'),
            'ref' => strtoupper(current(explode(' ', $sponsor)) . date('My')),
            'sponsor' => $sponsor
        ]);
    }

    public function sponsorsAction(Application $app)
    {
        return $this->render($app, 'sponsors.html.twig');
    }
}
