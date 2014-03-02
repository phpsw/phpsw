<?php

namespace PHPSW\Controller;

use DateTime,
    DMS\Service\Meetup\MeetupKeyAuthClient,
    Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Twitter;

class AppController
{
    public function indexAction(Request $request, Application $app)
    {
        $client = MeetupKeyAuthClient::factory(['key' => $app['meetup']['api']['key']]);

        $boards = $client->getDiscussionBoards([
            'urlname' => $app['meetup']['urlname']
        ]);

        $board = (object) current($boards->getData());

        $posts = $client
            ->getDiscussions([
                'urlname' => $app['meetup']['urlname'],
                'bid' => $board->id
            ])
        ;

        $posts = array_map(
            function ($post) use ($app) {
                $post = (object) $post;

                $post->last_post = (object) $post->last_post;
                $post->last_post->created_date = DateTime::createFromFormat('U', $post->last_post->created / 1000);
                $post->url = $app['meetup']['url'] . '/messages/boards/thread/' . $post->id;

                return $post;
            },
            $posts->getData()
        );

        $events = $client->getEvents([
            'group_urlname' => $app['meetup']['urlname'],
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

        $twitter = new Twitter(
            $app['twitter']['api']['key'],
            $app['twitter']['api']['secret'],
            $app['twitter']['access_token'],
            $app['twitter']['access_token_secret']
        );

        $tweets = $twitter->load(Twitter::ME);

        $tweets = array_map(
            function ($tweet) {
                // create xhtml safe text (mostly to be safe of ampersands)
                $tweet->html = htmlentities(html_entity_decode($tweet->text, ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');

                // for tweets, let's extract the urls from the entities object
                foreach ($tweet->entities->urls as $url) {
                    $old_url        = $url->url;
                    $expanded_url   = (empty($url->expanded_url))   ? $url->url : $url->expanded_url;
                    $display_url    = (empty($url->display_url))    ? $url->url : $url->display_url;
                    $replacement    = '<a href="' . $expanded_url . '" rel="external">' . $display_url . '</a>';
                    $tweet->html    = str_ireplace($old_url, $replacement, $tweet->html);
                }

                // let's extract the hashtags from the entities object
                foreach ($tweet->entities->hashtags as $hashtags) {
                    $hashtag        = '#' . $hashtags->text;
                    $replacement    = '<a href="https://twitter.com/search?q=%23' . $hashtags->text . '" rel="external">' . $hashtag . '</a>';
                    $tweet->html    = str_ireplace($hashtag, $replacement, $tweet->html);
                }

                // let's extract the usernames from the entities object
                foreach ($tweet->entities->user_mentions as $user_mentions) {
                    $username       = '@' . $user_mentions->screen_name;
                    $replacement    = '<a href="https://twitter.com/' . $user_mentions->screen_name . '" rel="external" title="' . $user_mentions->name . ' on Twitter">' . $username . '</a>';
                    $tweet->html    = str_ireplace($username, $replacement, $tweet->html);
                }

                // if we have media attached, let's extract those from the entities as well
                if (isset($tweet->entities->media)) {
                    foreach ($tweet->entities->media as $key => $media) {
                        $media->expanded_url_https = preg_replace('#^http://#', 'https://', $media->expanded_url);

                        $old_url        = $media->url;
                        $replacement    = '<a href="' . $media->expanded_url_https . '" class="twitter-media" data-media="' . $media->media_url_https . '" rel="external">' . $media->display_url . '</a>';
                        $tweet->html    = str_ireplace($old_url, $replacement, $tweet->html);

                        $tweet->entities->media[$key] = $media;
                    }
                }

                $tweet->url = 'https://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id;

                $tweet->created_date = new DateTime($tweet->created_at);

                return $tweet;
            },
            $tweets
        );

        return $app['twig']->render('index.html.twig', [
            'boards' => $boards,
            'events' => $events,
            'posts' => array_slice($posts, 0, 3),
            'tweets' => $tweets
        ]);
    }
}
