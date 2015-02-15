<?php

namespace PHPSW\Command\Redis;

use Knp\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Filesystem\Filesystem;

class DumpCommand extends Command
{
    protected function configure()
    {
        $this->setName('redis:dump-fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $fixtures = $app['console.project_directory'] . '/fixtures';
        $redis = $app['redis'];

        $this->fs = new Filesystem;
        $this->fs->exists($fixtures) or $this->fs->mkdir($fixtures);

        $keys = $redis->keys('*');

        sort($keys);

        foreach ($keys as $key) {
            echo $key . ': ';

            switch ($redis->type($key)) {
                case 'hash':
                    $dir = $fixtures . '/' . $key;

                    $this->fs->exists($dir) or $this->fs->mkdir($dir);

                    foreach ($redis->hgetall($key) as $key => $value) {
                        $this->write($dir . '/' . $key, $value);
                    }

                    break;

                case 'string':
                    $this->write($fixtures . '/' . $key, $redis->get($key));
            }

            echo PHP_EOL;
        }
    }

    protected function write($key, $value)
    {
        if (preg_match('#^\{|\[.*\]|\}$#', $value)) {
            $value = json_encode(json_decode($value), JSON_PRETTY_PRINT);
        }

        $this->fs->dumpFile($key, $value);

        echo '.';
    }
}
