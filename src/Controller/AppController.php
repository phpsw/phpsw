<?php

namespace PHPSW\Controller;

use DateTime,
    DMS\Service\Meetup\MeetupKeyAuthClient,
    Silex\Application,
    Symfony\Component\HttpFoundation\Request;

class AppController
{
    public function indexAction(Request $request, Application $app)
    {
        $meetup = (object) [
            'name' => 'PHPSW',
            'url' => 'http://www.meetup.com/PHPUGSW',
            'urlname' => 'PHPUGSW',
            'api' => (object) [
                'key' => null
            ]
        ];

        $twitter = (object) [
            'url' => 'http://twitter.com/phpsw',
            'user' => 'phpsw'
        ];

        require_once __DIR__ . '/../../config.php';

        $client = MeetupKeyAuthClient::factory(['key' => $meetup->api->key]);

        $boards = $client->getDiscussionBoards([
            'urlname' => $meetup->urlname
        ]);

        $board = (object) current($boards->getData());

        $posts = $client
            ->getDiscussions([
                'urlname' => $meetup->urlname,
                'bid' => $board->id
            ])
        ;

        $posts = array_map(
            function ($post) use ($meetup) {
                $post = (object) $post;

                $post->last_post = (object) $post->last_post;
                $post->last_post->created_date = DateTime::createFromFormat('U', $post->last_post->created / 1000);
                $post->url = $meetup->url . '/messages/boards/thread/' . $post->id;

                return $post;
            },
            $posts->getData()
        );

        $events = $client->getEvents([
            'group_urlname' => $meetup->urlname,
            'status' => implode(',', [
                'upcoming', 'past', 'proposed', 'suggested', 'cancelled'
            ]),
            'desc' => 'true'
        ]);

        $events = array_map(
            function ($event) {
                $event = (object) $event;

                $event->date = DateTime::createFromFormat('U', $event->time / 1000);
                $event->url = $event->event_url;
                $event->venue = (object) $event->venue;

                return $event;
            },
            iterator_to_array($events)
        );

        return $app['twig']->render('index.html.twig', [
            'boards' => $boards,
            'events' => $events,
            'meetup' => $meetup,
            'posts' => array_slice($posts, 0, 3),
            'twitter' => $twitter
        ]);
    }
}
