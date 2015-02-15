<?php

namespace PHPSW\Command;

use Knp\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class MeetupCommand extends Command
{
    protected function configure()
    {
        $this->setName('meetup:import:all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $this->meetup = $app['meetup.client'];
        $this->redis = $app['redis'];

        $tasks = [
            'group' => function ($callback) {
                if ($this->meetup->getGroup()) {
                    $this->set('group', $this->meetup->getGroup());

                    $callback();
                }
            },
            'events' => function ($callback) {
                foreach ($this->meetup->getEvents() as $event) {
                    $this->hset('events', $event->id, $event);

                    $callback();
                }
            },
            'photos' => function ($callback) {
                foreach ($this->meetup->getEvents() as $event) {
                    foreach ($event->photos as $photo) {
                        $this->hset('photos', $photo->id, $photo);

                        $callback();
                    }
                }
            },
            'posts' => function ($callback) {
                foreach ($this->meetup->getPosts() as $post) {
                    $this->hset('posts', $post->id, $post);

                    $callback();
                }
            },
            'reviews' => function ($callback) {
                foreach ($this->meetup->getReviews() as $review) {
                    $this->hset('reviews', $review->member_id, $review);

                    $callback();
                }
            },
            'members' => function ($callback) {
                foreach ($this->meetup->getMembers() as $member) {
                    $this->hset('members', $member->id, $member);

                    $callback();
                }
            },
            'speakers' => function ($callback) {
                foreach (array_reverse($this->meetup->getEvents()) as $event) {
                    foreach ($event->talks as $talk) {
                        $a = $this->hget('speakers', $talk->speaker->slug);
                        $b = $talk->speaker;

                        if (isset($a->member) && !isset($b->member)) {
                            $speaker = (object) array_replace_recursive((array) $b, (array) $a);
                        } elseif (!isset($a->member) && isset($b->member)) {
                            $speaker = (object) array_replace_recursive((array) $a, (array) $b);
                        } else {
                            $speaker = $b;
                        }

                        if (isset($speaker->member)) {
                            $speaker->photos = $this->meetup->getTaggedPhotos($speaker->member->member_id);
                        } else {
                            $speaker->photos = [];
                        }

                        $this->hset('speakers', $talk->speaker->slug, $speaker);

                        $callback();
                    }
                }
            },
            'talks' => function ($callback) {
                foreach ($this->meetup->getEvents() as $event) {
                    foreach ($event->talks as $talk) {
                        $this->hset('talks', $talk->id, $talk);

                        $callback();
                    }
                }
            }
        ];

        foreach ($tasks as $type => $task) {
            echo ucfirst($type) . ': ';

            $task(function () {
                echo '.';
            });

            sleep(1);

            echo PHP_EOL;
        }
    }

    protected function get($key)
    {
        return json_decode($this->redis->get($key));
    }

    protected function set($key, $value)
    {
        return $this->redis->set($key, json_encode($value, JSON_PRETTY_PRINT));
    }

    protected function hget($key, $hkey)
    {
        return json_decode($this->redis->hget($key, $hkey));
    }

    protected function hset($key, $hkey, $hvalue)
    {
        return $this->redis->hset($key, $hkey, json_encode($hvalue, JSON_PRETTY_PRINT));
    }
}
