<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../app/app.php';

$app->get('/', 'PHPSW\Controller\AppController::indexAction');
$app->get('/meetup/events', 'PHPSW\Controller\MeetupController::eventsAction');
$app->get('/meetup/photos', 'PHPSW\Controller\MeetupController::photosAction');
$app->get('/meetup/posts', 'PHPSW\Controller\MeetupController::postsAction');
$app->get('/meetup/reviews', 'PHPSW\Controller\MeetupController::reviewsAction');
$app->get('/meetup/sponsors', 'PHPSW\Controller\MeetupController::sponsorsAction');
$app->get('/twitter/tweets', 'PHPSW\Controller\TwitterController::tweetsAction');

$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views'
]);

$app->register(new Silex\Provider\UrlGeneratorServiceProvider);

$app->run();
