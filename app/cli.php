<?php

$app = require_once __DIR__ . '/app.php';

$app['cli'] = true;
$app['swiftmailer.use_spool'] = false;

$app->register(new Knp\Provider\ConsoleServiceProvider, array(
    'console.name' => 'PHPSW',
    'console.version' => '0.1.0',
    'console.project_directory' => __DIR__ . '/..'
));

$console = $app['console'];
$console->add(new PHPSW\Command\ActivityCommand);
$console->add(new PHPSW\Command\InvoiceCommand);
$console->add(new PHPSW\Command\JoindinCommand);
$console->add(new PHPSW\Command\MeetupCommand);
$console->add(new PHPSW\Command\TwitterCommand);
$console->add(new PHPSW\Command\YouTubeCommand);

if (in_array($app['env'], ['dev', 'testing'])) {
    $console->add(new PHPSW\Command\Redis\DumpCommand);
    $console->add(new PHPSW\Command\Redis\RestoreCommand);
}

return $app;
