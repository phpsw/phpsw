<?php

namespace PHPSW\Command;

use Knp\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class InvoiceCommand extends Command
{
    protected $cc = false;

    protected function configure()
    {
        $this->setName('invoice:all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tasks = [
            'invoice' => function ($callback) {
                $app = $this->getSilexApplication();

                $date = new \DateTime('next month');
                $sponsors = array_filter($app['sponsors']['meetup'], function ($sponsor) use ($date) {
                    $sponsor = (object) $sponsor;

                    $start = $sponsor->start ? \DateTime::createFromFormat('U', $sponsor->start) : null;
                    $end   = $sponsor->end   ? \DateTime::createFromFormat('U', $sponsor->end)   : null;

                    return ($date >= $start || !$start) && ($date <= $end || !$end);
                });

                foreach ($sponsors as $slug => $sponsor) {
                    $ref = strtoupper($slug . date('My', strtotime('next month')));
                    $path = __DIR__ . "/../../invoices/{$date->format('Y-m')}/{$ref}.pdf";
                    $token = md5(json_encode((object) [
                        'amount'   => $app['sponsorship'],
                        'invoiced' => date('Y-m-d'),
                        'secret'   => $app['secret'],
                        'slug'     => $slug
                    ]));
                    $url = "http://phpsw.uk/invoice/{$token}";

                    if (!is_dir(dirname($path))) mkdir(dirname($path));

                    exec("wkhtmltopdf '{$url}' {$path}", $output, $exit);

                    if ($exit === 0) {
                        $email = $app['mailer']->createMessage()
                            ->setSubject("PHPSW Invoice #{$ref}")
                            ->setFrom($app['email'])
                            ->setTo('steve@phpsw.uk')
                            ->setBody($app['twig']->render(
                                'emails/invoice.txt.twig',
                                array_merge($sponsor, ['url' => $url])
                            ))
                            ->attach(\Swift_Attachment::fromPath($path))
                        ;

                        if ($app->mail($email)) $callback();
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
