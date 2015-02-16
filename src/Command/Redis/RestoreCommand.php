<?php

namespace PHPSW\Command\Redis;

use Knp\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Finder\Finder;

class RestoreCommand extends Command
{
    protected function configure()
    {
        $this->setName('redis:restore-fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $fixtures = $app['console.project_directory'] . '/fixtures';
        $redis = $app['redis'];

        foreach (Finder::create()->depth(0)->in($fixtures) as $node) {
            $hash = $node->getFilename();

            echo $hash . ': ';

            if ($node->isDir()) {
                foreach (Finder::create()->files()->in($node->getPathname())->sortByName() as $file) {
                    $key = $file->getFilename();

                    $redis->hset($hash, $key, $this->parse($file->getContents()));

                    echo '.';
                }
            } else {
                $redis->set($hash, $this->parse($node->getContents()));

                echo '.';
            }

            echo PHP_EOL;
        }
    }

    protected function parse($value)
    {
        if (preg_match('#^\{|\[.*\]|\}$#', $value)) {
            $value = json_encode(json_decode($value));
        }

        return $value;
    }
}
