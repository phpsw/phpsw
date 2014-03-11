<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class TwitterController
{
    public function photoAction(Application $app, $user, $size = 'normal')
    {
        try {
            $response = $app['guzzle']->get('https://twitter.com/api/users/profile_image/' . $user . '?size=' . $size)->send();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $app->abort($e->getRequest()->getResponse()->getStatusCode());
        }

        return new Response(
            $response->getBody(),
            $response->getStatusCode(),
            [
                'Cache-Control' => 'public',
                'Content-Type' => (string) $response->getHeader('Content-Type'),
                'Expires' => (new \DateTime('+2 weeks'))->format('D, d M Y H:i:s T')
            ]
        );
    }

    public function tweetsAction(Application $app)
    {
        $tweets = array_map(
            function ($tweet) {
                $tweet = json_decode($tweet);

                $tweet->created_date = new \DateTime($tweet->created_at);

                return $tweet;
            },
            $app['redis']->hgetall('phpsw:tweets')
        );

        uasort($tweets, function($a, $b) {
            return $a->created_date < $b->created_date;
        });

        return $app['twig']->render('twitter/tweets.html.twig', [
            'tweets' => $tweets
        ]);
    }
}
