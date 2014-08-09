<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Response;

class MeetupController extends AbstractController
{
    public function postsAction(Application $app)
    {
        return $app['twig']->render('meetup/posts.html.twig', [
            'posts' => $app['meetup.client']->getPosts()
        ]);
    }

    public function photoAction(Application $app, $id, $size)
    {
        $photo = json_decode($app['redis']->hget('phpsw:photos', $id));

        try {
            $response = $app['guzzle']->get($photo->{"${size}_link"});
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $app->abort($e->getResponse()->getStatusCode());
        }

        return new Response($response->getBody(), $response->getStatusCode(), [
            'Cache-Control' => 'public',
            'Content-Type' => (string) $response->getHeader('Content-Type'),
            'Expires' => (new \DateTime('+2 weeks'))->format('D, d M Y H:i:s T')
        ]);
    }

    public function photosAction(Application $app)
    {
        $group = $app['meetup.client']->getGroup();

        return $app['twig']->render('meetup/photos.html.twig', [
            'photos' => $group ? $group->photos : []
        ]);
    }

    public function reviewsAction(Application $app)
    {
        return $app['twig']->render('meetup/reviews.html.twig', [
            'reviews' => $app['meetup.client']->getReviews()
        ]);
    }
}
