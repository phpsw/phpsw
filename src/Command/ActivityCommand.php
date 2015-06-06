<?php

namespace PHPSW\Command;

use Knp\Command\Command,
    Symfony\Component\DomCrawler\Crawler,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class ActivityCommand extends Command
{
    protected $cc = false;

    protected function configure()
    {
        $this->setName('activity:scrape');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app    = $this->getSilexApplication();
        $guzzle = $app['guzzle'];
        $redis  = $app['redis'];

        $tasks = [
            'phphants' => function ($callback, $url = 'http://phphants.co.uk/videos') use ($guzzle) {
                $crawler = new Crawler((string) $guzzle->get($url)->getBody());

                $crawler->filter('ul#videos li')->each(function (Crawler $node, $i) use ($callback) {
                    preg_match('#//www\.youtube-nocookie\.com/embed/(.*)\?rel=0#', $node->filter('iframe')->attr('src'), $matches);
                    list($_, $videoId) = $matches;

                    preg_match('#^by (.*) on (.*)$#', $node->filter('.speaker')->text(), $matches);
                    list($_, $speaker, $date) = $matches;

                    $callback((object) [
                        'title'    => $node->filter('.title')->text(),
                        'datetime' => new \DateTime($date, new \DateTimeZone('UTC')),
                        'speaker'  => $speaker,
                        'video'    => "https://www.youtube.com/watch?v={$videoId}"
                    ]);
                });
            },
            'phpdorset' => function ($callback, $url = 'http://www.phpdorset.co.uk/talks') use ($guzzle) {
                $list = new Crawler((string) $guzzle->get($url)->getBody());

                $list->filter('.talk_list .pod')->each(function (Crawler $node, $i) use ($callback, $guzzle) {
                    $url = "http://www.phpdorset.co.uk{$node->filter('.btn')->attr('href')}";

                    $content = (string) $guzzle->get($url)->getBody();
                    $page = new Crawler($content);

                    preg_match('#^(.*) - (.* \d{4})$#', trim($page->filter('h2')->text()), $matches);
                    list($_, $title, $date) = $matches;

                    if (preg_match('#player\.vimeo\.com/video/(\d+)#', $content, $matches)) {
                        list($_, $videoId) = $matches;
                    }

                    $callback((object) [
                        'title'    => $title,
                        'datetime' => new \DateTime($date, new \DateTimeZone('UTC')),
                        'speaker'  => trim($page->filter('.abstract .bio')->text()),
                        'abstract' => trim($page->filter('.abstract .text')->text()),
                        'video'    => isset($videoId) ? "https://vimeo.com/{$videoId}" : null
                    ]);
                });
            }
        ];

        foreach ($tasks as $source => $task) {
            echo ucfirst($source), ': ';

            $task(function ($a) use ($redis, $source) {
                $a->slug = $a->datetime->format('Ymd') . '-' . $this->slugify($a->title);
                $a->datetime = $a->datetime->format('c');
                $a->source = $source;

                $redis->hset('activity', $a->slug, json_encode($a));

                echo '.';
            });

            echo PHP_EOL;
        }
    }

    protected function slugify($string)
    {
        return $this->getSilexApplication()['slugify']->slugify(
            preg_replace(['#\'#', '#\s*&\s#'], ['', ' and '], $string)
        );
    }
}
