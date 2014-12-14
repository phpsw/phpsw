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

    public function conductAction(Application $app)
    {
        return $this->render($app, 'code-of-conduct.html.twig');
    }

    public function invoiceAction(Application $app, Request $request)
    {
        $sponsors = $app['sponsors'];

        $slug = $request->get('sponsor');

        if (isset($sponsors['event'][$slug])) {
            $sponsor = $sponsors['event'][$slug];
        } elseif (isset($sponsors['meetup'][$slug])) {
            $sponsor = $sponsors['meetup'][$slug];
        } else {
            $sponsor = (object) [
                'name'    => $request->get('name'),
                'company' => $request->get('company')
            ];
        }

        return $this->render($app, 'invoice.html.twig', [
            'amount' => $request->get('amount'),
            'currency' => $request->get('currency', '£'),
            'ref' => strtoupper($slug . date('My', strtotime('next month'))),
            'sponsor' => $sponsor
        ]);
    }

    public function sponsorsAction(Application $app)
    {
        return $this->render($app, 'sponsors.html.twig');
    }

    public function vouchersAction(Application $app)
    {
        return $this->render($app, 'vouchers.html.twig', [
            'prizes' => $app['vouchers']
        ]);
    }
}
