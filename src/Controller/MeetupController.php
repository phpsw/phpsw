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

    public function photosAction(Application $app)
    {
        $group = $app['meetup.client']->getGroup();

        if ($group->photos) {
            $photos = array_filter($group->photos, function ($photo) {
                return isset($photo->photo_album->event_id);
            });
        } else {
            $photos = [];
        }

        return $app['twig']->render('meetup/photos.html.twig', [
            'photos' => $photos
        ]);
    }

    public function reviewsAction(Application $app)
    {
        return $app['twig']->render('meetup/reviews.html.twig', [
            'reviews' => $app['meetup.client']->getReviews()
        ]);
    }
}
