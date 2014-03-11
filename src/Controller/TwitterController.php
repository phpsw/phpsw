<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class TwitterController
{
    public function photoAction(Application $app, $user, $size = 'normal')
    {
        $response = $app['guzzle']->get('https://twitter.com/api/users/profile_image/' . $user . '?size=' . $size)->send();

        return new Response(
            $response->getBody(),
            $response->getStatusCode(),
            array_merge(
                $response->getHeaders()->toArray(),
                ['Expires' => date('D, d M Y H:i:s T', strtotime('+2 weeks'))]
            )
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
