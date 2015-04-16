<?php

namespace PHPSW\Command;

use Knp\Command\Command,
    Madcoda\Youtube,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;



class YouTubeCommand extends Command
{
    protected function configure()
    {
        $this->setName('youtube:import:all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $this->meetup = $app['meetup.client'];
        $this->youtube = $app['youtube.client'];
        $this->redis = $app['redis'];

        $events = $this->hgetall('events');

        $tasks = [
            'videos' => function ($callback) use ($app, $events) {
                $playlists = $this->youtube->getPlaylistsByChannelId($app['youtube']['channel']['id']);

                foreach ($playlists as $playlist) {
                    list($name, $date) = explode(', ', $playlist->snippet->title);

                    $event = current(array_filter(
                        $events,
                        function ($event) use ($name, $date) {
                            return $event->name == $name && (new \DateTime($event->date->date))->format('F Y') == $date;
                        }
                    ));

                    $videos = $this->youtube->getPlaylistItemsByPlaylistId($playlist->id);

                    foreach ($videos as $video) {
                        $talk = current(array_filter(
                            $event->talks,
                            function ($talk) use ($video) {
                                return stripos($video->snippet->title, $talk->title) === 0;
                            }
                        ));

                        $this->hset('videos', $talk->id, "https://www.youtube.com/embed/{$video->contentDetails->videoId}");

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

    protected function hgetall($key)
    {
        return array_map(
            function ($data) {
                return json_decode($data);
            },
            $this->redis->hgetall($key)
        );
    }

    protected function hset($key, $hkey, $hvalue)
    {
        return $this->redis->hset($key, $hkey, $hvalue);
    }
}
