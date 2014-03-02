<?php

namespace PHPSW\API;

use DMS\Service\Meetup\MeetupKeyAuthClient;

class Meetup
{
    protected $client;

    protected $config;

    protected $events;

    public function __construct(array $config)
    {
        $this->client = MeetupKeyAuthClient::factory(['key' => $config['api']['key']]);
        $this->config = $config;
    }

    public function getEvents()
    {
        if ($this->events === null) {
            $events = $this->client->getEvents([
                'group_urlname' => $this->config['urlname'],
                'status' => implode(',', [
                    'upcoming', 'past', 'proposed', 'suggested', 'cancelled'
                ]),
                'desc' => 'true'
            ]);

            $this->events = array_map(
                function ($event) {
                    $event = (object) $event;

                    $event->date = \DateTime::createFromFormat('U', $event->time / 1000);
                    $event->url = $event->event_url;
                    $event->venue = (object) $event->venue;

                    if ($event->status == 'upcoming') {
                        $event->rsvps = iterator_to_array($this->client->getRSVPs(['event_id' => $event->id]));
                    }

                    return $event;
                },
                iterator_to_array($events)
            );
        }

        return $this->events;
    }

    public function getPastEvents()
    {
        return array_filter($this->getEvents(), function ($event) {
            return $event->status == 'past';
        });
    }

    public function getUpcomingEvents()
    {
        return array_filter($this->getEvents(), function ($event) {
            return $event->status == 'upcoming';
        });
    }

    public function getDiscussionBoard()
    {
        return current($this->getDiscussionBoards());
    }

    public function getDiscussionBoards()
    {
        $boards = $this->client->getDiscussionBoards([
            'urlname' => $this->config['urlname']
        ]);

        return array_map(
            function ($board) {
                return (object) $board;
            },
            $boards->getData()
        );
    }

    public function getPosts($board = null)
    {
        if ($board === null) {
            $board = $this->getDiscussionBoard();
        }

        $posts = $this->client->getDiscussions([
            'urlname' => $this->config['urlname'],
            'bid' => $board->id
        ]);

        return array_map(
            function ($post) {
                $post = (object) $post;

                $post->last_post = (object) $post->last_post;
                $post->last_post->created_date = \DateTime::createFromFormat('U', $post->last_post->created / 1000);
                $post->url = $this->config['url'] . '/messages/boards/thread/' . $post->id;

                return $post;
            },
            $posts->getData()
        );
    }
}
