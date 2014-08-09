<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Response;

class TwitterController extends AbstractController
{
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
