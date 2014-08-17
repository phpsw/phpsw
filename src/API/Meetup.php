<?php

namespace PHPSW\API;

use DMS\Service\Meetup\MeetupKeyAuthClient,
    Symfony\Component\DomCrawler\Crawler;

class Meetup
{
    protected $cli;
    protected $client;
    protected $config;
    protected $debug;
    protected $events;
    protected $group;
    protected $members;
    protected $photos;
    protected $posts;
    protected $reviews;
    protected $speakers;
    protected $talks;

    public function __construct($app, array $config, $cli, $debug = false)
    {
        if ($cli) {
            $this->client = MeetupKeyAuthClient::factory(['key' => $config['api']['key']]);
        }

        $this->app = $app;
        $this->cli = $cli;
        $this->debug = $debug;
        $this->config = $config;
        $this->redis = $app['redis'];
    }

    public function getGroup()
    {
        if ($this->group === null) {
            if ($this->cli) {
                $response = $this->client->getGroups([
                    'group_urlname' => $this->config['urlname'],
                    'fields' => implode(',', ['photos', 'sponsors'])
                ]);

                $this->group = (object) current($response->getData());

                $this->group->photos = $this->getPhotosFromApi();

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
        }

        return $this->group;
    }

    public function getEvents()
    {
        if ($this->events === null) {
            if ($this->cli) {
                $this->events = $this->getEventsFromApi();
            } else {
                $this->events = $this->getEventsFromCache();
            }

            $this->events = array_map(
                function ($event) {
                    $event->date = \DateTime::createFromFormat('U', $event->time / 1000);

                    if ($this->cli && !$this->debug || !$this->cli && $this->debug) {
                        $event = $this->parse($event);
                    }

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

    public function getEvent($id)
    {
        $events = $this->getEvents();

        if (array_key_exists($id, $events)) {
            return $events[$id];
        }
    }

    public function getDraftEvents()
    {
        return array_reverse(
            array_filter($this->getEvents(), function ($event) {
                return $event->status == 'draft';
            })
        );
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
            if ($this->cli) {
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

    public function getMember($id)
    {
        return $this->getMembers()[$id];
    }

    public function getMembers()
    {
        if ($this->members === null) {
            if ($this->cli) {
                $this->members = array_replace_recursive(
                    $this->client->getMembers(['group_urlname' => $this->config['urlname'], 'order' => 'name'])->getData(),
                    $this->client->getGroupProfiles(['group_urlname' => $this->config['urlname'], 'order' => 'name'])->getData()
                );
            } else {
                $this->members = array_map(
                    function ($member) {
                        $member = json_decode($member);

                        $member->joined = \DateTime::createFromFormat('U', $member->joined / 1000);

                        return $member;
                    },
                    $this->redis->hgetall('phpsw:members')
                );
            }

            $this->members = array_combine(
                array_map(
                    function ($member) {
                        $member = (object) $member;

                        return $member->id;
                    },
                    $this->members
                ),
                array_map(
                    function ($member) {
                        $member = (object) $member;

                        if (isset($member->photo)) {
                            $photo = $member->photo = (object) $member->photo;

                            if (isset($photo->highres_link)) {
                                $member->photo->url = $photo->highres_link;
                            } elseif (isset($photo->photo_link)) {
                                $member->photo->url = $photo->photo_link;
                            } elseif (isset($photo->thumb_link)) {
                                $member->photo->url = $photo->thumb_link;
                            }
                        }

                        $member->other_services = array_map(
                            function ($service) {
                                return (object) $service;
                            },
                            (array) $member->other_services
                        );

                        $member->visited_date = \DateTime::createFromFormat('U', ($member->visited / 1000));

                        return $member;
                    },
                    $this->members
                )
            );
        }

        return $this->members;
    }

    public function getReviews()
    {
        if ($this->reviews === null) {
            if ($this->cli) {
                $this->reviews = $this->client->getComments(['group_urlname' => $this->config['urlname']])->getData();
            } else {
                $this->reviews = array_map(
                    function ($review) {
                        $review = json_decode($review);

                        return $review;
                    },
                    $this->redis->hgetall('phpsw:reviews')
                );
            }

            $this->reviews = array_map(
                function ($review) {
                    $review = (object) $review;

                    $review->created_date = new \DateTime($review->created);

                    return $review;
                },
                $this->reviews
            );
        }

        return $this->reviews;
    }

    public function getSpeakers()
    {
        if ($this->speakers === null) {
            $this->speakers = array_map(
                function ($speaker) {
                    $speaker = json_decode($speaker);

                    $speaker->talks = array_filter(
                        $this->getTalks(),
                        function ($talk) use ($speaker) {
                            return $talk->speaker->id == $speaker->id;
                        }
                    );

                    uasort($speaker->talks, function($a, $b) {
                        return $a->event->date < $b->event->date;
                    });

                    return $speaker;
                },
                $this->redis->hgetall('phpsw:speakers')
            );

            usort($this->speakers, function ($a, $b) {
                return $a->name > $b->name ? 1 : -1;
            });
        }

        return $this->speakers;
    }

    public function getSpeaker($slug)
    {
        foreach ($this->getSpeakers() as $speaker) {
            if ($speaker->slug == $slug) {
                return $speaker;
            }
        }
    }

    public function getTalks()
    {
        if ($this->talks === null) {
            $this->talks = array_map(
                function ($talk) {
                    $talk = json_decode($talk);

                    $talk->event = $this->getEvent($talk->event);

                    return $talk;
                },
                $this->redis->hgetall('phpsw:talks')
            );
        }

        return $this->talks;
    }

    protected function getEventsFromApi()
    {
        $events = $this->client->getEvents([
            'group_urlname' => $this->config['urlname'],
            'status' => implode(',', [
                'upcoming', 'draft', 'past', 'proposed', 'suggested', 'cancelled'
            ]),
            'desc' => 'true'
        ]);

        return array_map(
            function ($event) {
                $event = (object) $event;

                if (isset($event->description)) {
                    $event->description = preg_replace(
                        '#<a href="mailto:.*">(.*)@(.*)\.(.*)</a>#',
                        '<a href="mailto:\1 at \2 dot \3" class="email">\1 at \2 dot \3</a>',
                        $event->description
                    );
                } else {
                    $event->description = null;
                }

                $event->slug = $this->slugify($event->name);

                $event->photos = array_values(
                    array_filter($this->getGroup()->photos, function ($photo) use ($event) {
                        $album = $photo->photo_album;

                        return isset($album->event_id) && $album->event_id == $event->id;
                    })
                );

                $event->rsvps = iterator_to_array($this->client->getRSVPs(['event_id' => $event->id]));
                $event->talks = [];
                $event->url = $event->event_url;
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

    protected function getPhotosFromApi()
    {
        if ($this->photos === null) {
            $this->photos = array_map(
                function ($photo) {
                    $photo = (object) $photo;

                    $photo->id = $photo->photo_id;
                    $photo->member = (object) $photo->member;
                    $photo->photo_album = (object) $photo->photo_album;

                    return $photo;
                },
                $this->client->getPhotos(['group_urlname' => $this->config['urlname']])->getData()
            );
        }

        return $this->photos;
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

    protected function crawl($html)
    {
        $crawler = new Crawler;

        $crawler->addHTMLContent($html, 'UTF-8');

        return $crawler;
    }

    protected function parse($event)
    {
        $crawler = $this->crawl($event->description);

        if ($crawler->count()) {
            $crawler = $crawler->children()->first();

            $event->description = $crawler->html();

            $crawler = $crawler
                ->filter('p')
                ->reduce(function (Crawler $node) {
                    return (boolean) preg_match('#^-[^-]#', $node->text());
                })
            ;

            foreach ($crawler as $i => $node) {
                $node = new Crawler($node);

                $talk = (object) [
                    'description' => null
                ];

                $titleNode = $node->filter('b')->first();

                if ($titleNode->count()) {
                    $talk->title = preg_replace('#\s+#u', ' ', $titleNode->text());
                    $talk->id = $event->id . '-' . $this->slugify($talk->title);
                    $talk->event = $event->id;

                    $speakerAndOrg = explode(',', preg_replace('#-\s*' . preg_quote($titleNode->text()) . '#u', '', $node->text()), 2);
                    $speakerAndOrg = preg_replace('#^\s*(.*)\s*$#u', '\1', $speakerAndOrg);

                    $talk->speaker = (object) [
                        'id' => $this->slugify($speakerAndOrg[0]),
                        'name' => $speakerAndOrg[0],
                        'slug' => $this->slugify($speakerAndOrg[0])
                    ];

                    /* bio */

                    $nodeHtml = preg_replace('#\s+#u', ' ', $node->html());
                    $titleHtml = preg_replace('#\s+#u', ' ', $titleNode->html());

                    $speakerHtml = trim(str_replace('<br>' , '', preg_replace('#- <b>' . preg_quote($titleHtml) . '</b>#u', '', $nodeHtml)));

                    $speakerExplode = explode(', ', $speakerHtml, 2);

                    if (isset($speakerExplode[1])) {
                        $talk->speaker->bio = $speakerExplode[1];
                    }

                    /* end */

                    $nodesAfterTitleNode = $titleNode->nextAll()->filter('a');

                    $speakerNode = $nodesAfterTitleNode->first();

                    if ($speakerNode->count()) {
                        $talk->speaker->url = $speakerNode->attr('href');

                        if (preg_match('#http://www.meetup.com/php-sw/members/([^\/]+)#', $talk->speaker->url, $matches)) {
                            $talk->speaker->member = $this->getMember($matches[1]);

                            if (isset($talk->speaker->member->photo)) {
                                $photo = $talk->speaker->photo = $talk->speaker->member->photo;

                                if (isset($photo->highres_link)) {
                                    $talk->speaker->url = $photo->highres_link;
                                } elseif (isset($photo->photo_link)) {
                                    $talk->speaker->url = $photo->photo_link;
                                } elseif (isset($photo->thumb_link)) {
                                    $talk->speaker->url = $photo->thumb_link;
                                }
                            }

                            foreach ($talk->speaker->member->other_services as $key => $service) {
                                $talk->speaker->$key = preg_replace('#^@#', '', basename($service->identifier));
                            }
                        }

                        if (preg_match('#https://twitter.com/([^\/]+)#', $talk->speaker->url, $matches)) {
                            $talk->speaker->twitter = $matches[1];

                            $talk->speaker->photo = (object) [
                                'thumb_link' => "https://twitter.com/api/users/profile_image/{$talk->speaker->twitter}?size=bigger",
                                'photo_link' => "https://twitter.com/api/users/profile_image/{$talk->speaker->twitter}?size=original",
                                'highres_link' => "https://twitter.com/api/users/profile_image/{$talk->speaker->twitter}?size=original",
                                'url' => "https://twitter.com/api/users/profile_image/{$talk->speaker->twitter}?size=original"
                            ];
                        }
                    }

                    $orgNode = $nodesAfterTitleNode->eq(1);

                    if ($orgNode->count()) {
                        $talk->speaker->organisation = (object) [
                            'name' => $orgNode->text(),
                            'url' => $orgNode->attr('href')
                        ];
                    }

                    $parsed = false;

                    $talkDescriptionNode = $speakerNode->parents()->first()->nextAll()->filter('p');

                    $talkDescriptionNode->each(function ($node) use ($event, $talk, &$parsed) {
                        if (preg_match('#^\s*\-#', $node->text())) $parsed = true; # break if is next speaker or hr
                        if (strpos($node->html(), '<a') !== false) $parsed = true; # break if has links
                        if (preg_match('#BaseKit|Brightpearl|Doors#', $node->text())) $parsed = true; # sad hacks to fix some edge cases

                        if (!$parsed) {
                            $event->description = str_replace('<p>' . $node->html() . '</p>', '', $event->description);
                            $talk->description .= '<p>' . preg_replace('#^\s*<br>#', '', preg_replace('#<br>\s*$#', '', $node->html())) . '</p>' . PHP_EOL;
                        }
                    });

                    $talk->slides = $this->redis->hget('phpsw:slides', $talk->id);

                    $event->description = str_replace('<p>' . $node->html() . '</p>', '', $event->description);
                    $event->talks[] = $talk;
                }
            }

            // strip blank p's
            $event->description = preg_replace('#<p>\s*</p>#u', '', $event->description);

            // strip leading and trailings br's and whitespace in p's
            $event->description = preg_replace(['#<p>\s*(<br>)*#u', '#(<br>)*\s*</p>#u'], ['<p>', '</p>'], $event->description);

            // hr up any dashes
            $event->description = preg_replace('#<p>--</p>#', '<hr>', $event->description);

            // html entityify everything
            $event->description = htmlentities($event->description, ENT_NOQUOTES, 'UTF-8', false);

            // html de-entityify tags
            $event->description = str_replace(['&lt;', '&gt;'], ['<', '>'], $event->description);
        } else {
            // strip crazy </p> descriptions meetup returns
            $event->description = null;
        }

        return $event;
    }

    protected function slugify($string)
    {
        return $this->app['slugify']->slugify(
            preg_replace(['#\'#', '#\s*&\s#'], ['', ' and '], $string)
        );
    }
}
