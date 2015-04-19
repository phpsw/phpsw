<?php

namespace PHPSW\Command;

use Knp\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class JoindinCommand extends Command
{
    protected function configure()
    {
        $this->setName('joindin:import:all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $this->joindin = $app['joindin.client'];
        $this->meetup  = $app['meetup.client'];
        $this->redis   = $app['redis'];

        $tasks = [
            'feedback' => function ($callback) {
                $events = $this->joindin->get('http://api.joind.in/v2.1/events', ['tags' => 'phpsw'])->events;

                foreach ($events as $event) {
                    $comments = $this->joindin->get($event->all_talk_comments_uri)->comments;
                    $talks = $this->joindin->get($event->talks_uri)->talks;

                    foreach ($talks as $talk) {
                        $id = preg_replace('#.*/(\d+)-.*#', '\1', $event->href) . '-' . $this->meetup->slugify($talk->talk_title);

                        $this->redis->hset('feedback', $id, json_encode((object) [
                            'comments' => array_filter(
                                $comments,
                                function ($comment) use ($talk) {
                                    return $comment->talk_uri == $talk->uri;
                                }
                            ),
                            'duration' => $talk->duration,
                            'rating'   => $talk->average_rating,
                            'starred'  => $talk->starred_count,
                            'uri'      => str_replace('/talk/view', '', $talk->website_uri)
                        ]));

                        $callback();
                    }
                }
            }
        ];

        foreach ($tasks as $type => $task) {
            echo ucfirst($type), ': ';

            $task(function () {
                echo '.';
            });

            echo PHP_EOL;
        }
    }
}
