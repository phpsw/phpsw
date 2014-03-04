<?php

namespace PHPSW\Command;

use PHPSW\API\Meetup,
    Predis,
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

        $redis = new Predis\Client;

        $meetup = new Meetup($app['meetup']);

        foreach ($meetup->getEvents() as $event) {
            $redis->hset('events', $event->id, json_encode($event));
        }
    }
}
