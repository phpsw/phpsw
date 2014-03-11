<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../app/app.php';

$app->get('/', 'PHPSW\Controller\AppController::indexAction')->bind('home');
$app->get('/meetup/events', 'PHPSW\Controller\MeetupController::eventsAction')->bind('events');
$app->get('/meetup/photos', 'PHPSW\Controller\MeetupController::photosAction')->bind('photos');
$app->get('/meetup/posts', 'PHPSW\Controller\MeetupController::postsAction')->bind('posts');
$app->get('/meetup/reviews', 'PHPSW\Controller\MeetupController::reviewsAction')->bind('reviews');
$app->get('/meetup/sponsors', 'PHPSW\Controller\MeetupController::sponsorsAction')->bind('sponsors');
$app
    ->get('/twitter/{user}/photo/{size}', 'PHPSW\Controller\TwitterController::photoAction')
    ->bind('twitter_photo')
    ->assert('size', implode('|', ['bigger', 'normal', 'mini', 'original']))
    ->value('size', 'normal')
;
$app
    ->get('/twitter/{user}/tweets', 'PHPSW\Controller\TwitterController::tweetsAction')
    ->bind('tweets')
    ->assert('user', $app['twitter']['user'])
    ->value('user', $app['twitter']['user'])
;

$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views'
]);

$app->register(new Silex\Provider\UrlGeneratorServiceProvider);

$app->run();
