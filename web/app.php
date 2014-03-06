<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application;

if (strpos(__DIR__, 'phpsw.org.uk') !== false) {
    $env = 'prod';
} else {
    $env = 'dev';
}

$app->get('/', 'PHPSW\Controller\AppController::indexAction');
$app->get('/meetup/events', 'PHPSW\Controller\MeetupController::eventsAction');
$app->get('/meetup/posts', 'PHPSW\Controller\MeetupController::postsAction');
$app->get('/meetup/reviews', 'PHPSW\Controller\MeetupController::reviewsAction');
$app->get('/meetup/sponsors', 'PHPSW\Controller\MeetupController::sponsorsAction');
$app->get('/twitter/tweets', 'PHPSW\Controller\TwitterController::tweetsAction');

foreach (['app', $env, 'secrets'] as $config) {
    $app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/' . $config . '.yml'));
}

$app->register(new Knp\Provider\ConsoleServiceProvider, array(
    'console.name' => 'ConsoleApp',
    'console.version' => '1.0.0',
    'console.project_directory' => __DIR__ . '/..'
));

$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views'
]);

$app->register(new Silex\Provider\UrlGeneratorServiceProvider);

$app->run();
