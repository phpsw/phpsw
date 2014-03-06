<?php

namespace PHPSW\API;

use DMS\Service\Meetup\MeetupKeyAuthClient;

class Meetup
{
    protected $cache;

    protected $client;

    protected $config;

    protected $events;

    protected $group;

    protected $posts;

    public function __construct(array $config, $cache = true)
    {
        if (!$cache) {
            $this->client = MeetupKeyAuthClient::factory(['key' => $config['api']['key']]);
        } else {
            $this->redis = new \Predis\Client;
        }

        $this->cache = $cache;
        $this->config = $config;
    }

    public function getGroup()
    {
        if (!$this->cache) {
            $response = $this->client->getGroups([
                'group_urlname' => $this->config['urlname'],
                'fields' => implode(',', ['photos', 'sponsors'])
            ]);

            $this->group = (object) current($response->getData());

            $this->group->rating = (object) [
                'average' => $this->group->rating,
                'count' => array_sum(
                    array_map(
                        function ($event) {
                            return isset($event->rating) ? $event->rating['count'] : 0;
                        },
                        $this->getEvents()
                    )
                )
            ];
        } else {
            $this->group = json_decode($this->redis->get('phpsw:group'));
        }

        return $this->group;
    }

    public function getEvents()
    {
        if ($this->events === null) {
            if (!$this->cache) {
                $this->events = $this->getEventsFromApi();
            } else {
                $this->events = $this->getEventsFromCache();
            }

            $this->events = array_map(
                function ($event) {
                    $event->date = \DateTime::createFromFormat('U', $event->time / 1000);

                    return $event;
                },
                $this->events
            );

            uasort($this->events, function($a, $b) {
                return $a->date < $b->date;
            });
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
        return array_reverse(
            array_filter($this->getEvents(), function ($event) {
                return $event->status == 'upcoming';
            })
        );
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
        if ($this->posts === null) {
            if (!$this->cache) {
                $this->posts = $this->getPostsFromApi();
            } else {
                $this->posts = $this->getPostsFromCache();
            }

            $this->posts = array_map(
                function ($post) {
                    $post->last_post->created_date = \DateTime::createFromFormat('U', $post->last_post->created / 1000);

                    return $post;
                },
                $this->posts
            );

            uasort($this->posts, function($a, $b) {
                return $a->last_post->created_date < $b->last_post->created_date;
            });
        }

        return $this->posts;
    }

    protected function getEventsFromApi()
    {
        $events = $this->client->getEvents([
            'group_urlname' => $this->config['urlname'],
            'status' => implode(',', [
                'upcoming', 'past', 'proposed', 'suggested', 'cancelled'
            ]),
            'desc' => 'true'
        ]);

        return array_map(
            function ($event) {
                $event = (object) $event;

                $event->description = preg_replace(
                    '#<a href="mailto:.*">(.*)@(.*)</a>#',
                    '\1 at \2',
                    $event->description
                );

                $event->url = $event->event_url;
                $event->rsvps = iterator_to_array($this->client->getRSVPs(['event_id' => $event->id]));
                $event->venue = (object) $event->venue;

                return $event;
            },
            iterator_to_array($events)
        );
    }

    protected function getEventsFromCache()
    {
        return array_map(
            function ($event) {
                $event = json_decode($event);

                return $event;
            },
            $this->redis->hgetall('phpsw:events')
        );
    }

    protected function getPostsFromApi($board = null)
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
                $post->url = $this->config['url'] . '/messages/boards/thread/' . $post->id;

                return $post;
            },
            $posts->getData()
        );
    }

    protected function getPostsFromCache()
    {
        return array_map(
            function ($post) {
                $post = json_decode($post);

                return $post;
            },
            $this->redis->hgetall('phpsw:posts')
        );
    }
}
