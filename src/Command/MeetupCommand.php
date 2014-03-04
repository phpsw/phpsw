<?php

namespace PHPSW\Command;

use PHPSW\API\Meetup,
    Knp\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;

class MeetupCommand extends Command
{
    protected function configure()
    {
        $this->setName('meetup:import:all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $redis = new \Predis\Client;

        $meetup = new Meetup($app['meetup'], false);

        echo 'Events: ';

        foreach ($meetup->getEvents() as $event) {
            $redis->hset('phpsw:events', $event->id, json_encode($event));

            echo '.';
        }

        echo PHP_EOL;

        echo 'Posts: ';

        foreach ($meetup->getPosts() as $post) {
            $redis->hset('phpsw:posts', $post->id, json_encode($post));

            echo '.';
        }

        echo PHP_EOL;
    }
}
