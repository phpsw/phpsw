<?php

namespace PHPSW\Controller;

use Silex\Application;

class MeetupController extends AbstractController
{
    public function postsAction(Application $app)
    {
        return $app['twig']->render('meetup/posts.html.twig', [
            'posts' => $app['meetup.client']->getPosts()
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
