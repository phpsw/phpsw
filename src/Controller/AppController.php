<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController extends AbstractController
{
    public function indexAction(Application $app)
    {
        $meetup = $app['meetup.client'];

        $description = $meetup->getGroup()->description;
        $description = str_replace($app['website']['url'], $app['meetup']['url'], $description);
        $description = str_replace($app['website']['urlshort'], $app['meetup']['urlshort'], $description);
        $description = str_replace($app['meetup']['url'] . '/code-of-conduct', $app['website']['url'] . '/code-of-conduct', $description);

        return $this->render($app, 'index.html.twig', [
            'description' => $description,
            'events' => $meetup->getUpcomingEvents() ?: array_slice($meetup->getPastEvents(), 0, 1)
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

    public function errorAction(Application $app)
    {
        throw new \Exception();
    }

    public function invoiceAction(Application $app, Request $request, $token)
    {
        $interval = new \DateInterval('P1D');
        $invoiced = new \DateTime('2015-01-01');
        $today    = new \DateTime();

        while ($invoiced <= $today) {
            foreach ($app['sponsors']['meetup'] as $slug => $sponsor) {
                if ($token == md5(json_encode((object) [
                    'amount'   => $app['sponsorship'],
                    'invoiced' => $invoiced->format('Y-m-d'),
                    'secret'   => $app['secret'],
                    'slug'     => $slug
                ]))) {
                    return $this->render($app, 'invoice.html.twig', [
                        'amount'   => $request->get('amount', $app['sponsorship']),
                        'currency' => $request->get('currency', '£'),
                        'due'      => new \DateTime($request->get('due', 'last day of ' . $invoiced->format('F Y'))),
                        'invoiced' => $request->get('invoiced') ? new \DateTime($request->get('invoiced')) : $invoiced,
                        'ref'      => $request->get('ref', strtoupper($slug . $invoiced->add(new \DateInterval('P1M'))->format('My'))),
                        'sponsor'  => $request->get('sponsor', $sponsor['company'])
                    ]);
                }
            };

            $invoiced->add($interval);
        }

        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
