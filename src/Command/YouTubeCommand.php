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
    protected $cc = false;

    protected function configure()
    {
        $this->setName('youtube:import:all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $this->youtube = $app['youtube.client'];
        $this->redis = $app['redis'];

        $events = $this->hgetall('events');
        $talks = $this->hgetall('talks');

        $tasks = [
            'videos' => function ($success, $fail) use ($app, $events, $talks) {
                $playlists = $this->youtube->getPlaylistsByChannelId($app['youtube']['channel']['id']);

                foreach ($playlists as $playlist) {
                    list($name, $date) = explode(', ', $playlist->snippet->title);

                    $event = current(array_filter(
                        $events,
                        function ($event) use ($name, $date) {
                            return $event->name == $name && (new \DateTime($event->date->date))->format('F Y') == $date;
                        }
                    ));

                    $event->talks = array_filter(
                        $talks,
                        function ($talk) use ($event) {
                            return $talk->event == $event->id;
                        }
                    );

                    $videos = $this->youtube->getPlaylistItemsByPlaylistId($playlist->id);

                    foreach ($videos as $video) {
                        $talk = current(array_filter(
                            $event->talks,
                            function ($talk) use ($video) {
                                return stripos($video->snippet->title, $talk->title) === 0;
                            }
                        ));

                        if ($talk) {
                            $this->cc = $this->cc ?: !!$this->hset('videos', $talk->id, "https://www.youtube.com/watch?v={$video->contentDetails->videoId}");
                            $success();
                        } else {
                            $fail();
                        }

                    }
                }
            }
        ];

        foreach ($tasks as $type => $task) {
            echo ucfirst($type), ': ';

            $task(
                function () { echo '.'; },
                function () { echo 'x'; }
            );

            echo PHP_EOL;
        }

        if ($this->cc) $this->refresh();
    }

    protected function refresh()
    {
         exec('sudo service varnish restart');
         file_get_contents('http://phpsw.org.uk');
         file_get_contents('http://phpsw.org.uk/events');
         file_get_contents('http://phpsw.org.uk/speakers');
         file_get_contents('http://phpsw.org.uk/sponsors');
         file_get_contents('http://phpsw.org.uk/talks');
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

    protected function hget($key, $hkey)
    {
        return $this->redis->hget($key, $hkey);
    }

    protected function hset($key, $hkey, $hvalue)
    {
        return $this->redis->hset($key, $hkey, $hvalue);
    }
}
