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

        $redis = $app['redis'];
        $meetup = $app['meetup.client'];

        $tasks = [
            'group' => function ($callback) use ($meetup, $redis) {
                if ($meetup->getGroup()) {
                    $redis->set('phpsw:group', json_encode($meetup->getGroup()));

                    $callback();
                }
            },
            'events' => function ($callback) use ($meetup, $redis) {
                foreach ($meetup->getEvents() as $event) {
                    $redis->hset('phpsw:events', $event->id, json_encode($event));

                    $callback();
                }
            },
            'photos' => function ($callback) use ($meetup, $redis) {
                foreach ($meetup->getEvents() as $event) {
                    foreach ($event->photos as $photo) {
                        $redis->hset('phpsw:photos', $photo->id, json_encode($photo));

                        $callback();
                    }
                }
            },
            'posts' => function ($callback) use ($meetup, $redis) {
                foreach ($meetup->getPosts() as $post) {
                    $redis->hset('phpsw:posts', $post->id, json_encode($post));

                    $callback();
                }
            },
            'reviews' => function ($callback) use ($meetup, $redis) {
                foreach ($meetup->getReviews() as $review) {
                    $redis->hset('phpsw:reviews', $review->member_id, json_encode($review));

                    $callback();
                }
            },
            'members' => function ($callback) use ($meetup, $redis) {
                foreach ($meetup->getMembers() as $member) {
                    $redis->hset('phpsw:members', $member->id, json_encode($member));

                    $callback();
                }
            },
            'speakers' => function ($callback) use ($meetup, $redis) {
                foreach ($meetup->getEvents() as $event) {
                    foreach ($event->talks as $talk) {
                        $a = json_decode($redis->hget('phpsw:speakers', $talk->speaker->slug));
                        $b = $talk->speaker;

                        if (isset($a->member) && !isset($b->member)) {
                            $speaker = (object) array_replace_recursive((array) $b, (array) $a);
                        } elseif (!isset($a->member) && isset($b->member)) {
                            $speaker = (object) array_replace_recursive((array) $a, (array) $b);
                        } else {
                            $speaker = $b;
                        }

                        $redis->hset('phpsw:speakers', $talk->speaker->slug, json_encode($speaker));

                        $callback();
                    }
                }
            },
            'talks' => function ($callback) use ($meetup, $redis) {
                foreach ($meetup->getEvents() as $event) {
                    foreach ($event->talks as $talk) {
                        $redis->hset('phpsw:talks', $talk->id, json_encode($talk));

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

            echo PHP_EOL;
        }
    }
}
