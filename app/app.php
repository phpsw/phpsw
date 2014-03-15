<?php

$app = new PHPSW\Application;

if (strpos(__DIR__, 'phpsw.org.uk') !== false) {
    $app['env'] = 'prod';
} else {
    $app['env'] = 'dev';
}

$app['guzzle'] = new Guzzle\Http\Client;

$app['redis'] = new Predis\Client;

$app['meetup.client'] = function ($app) {
    return new PHPSW\API\Meetup($app, $app['meetup'], $app['cli'], $app['debug']);
};

$app['twitter.client'] = function ($app) {
    return new Twitter(
        $app['twitter']['api']['key'],
        $app['twitter']['api']['secret'],
        $app['twitter']['access_token'],
        $app['twitter']['access_token_secret']
    );
};

foreach (['app', $app['env'], 'secrets'] as $config) {
    if (file_exists(__DIR__ . '/../config/' . $config . '.yml')) {
        $app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/' . $config . '.yml'));
    }
}

$app->get('/', 'PHPSW\Controller\AppController::indexAction')->bind('home');
$app->get('/meetup/events', 'PHPSW\Controller\MeetupController::eventsAction')->bind('events');
$app->get('/meetup/photos', 'PHPSW\Controller\MeetupController::photosAction')->bind('photos');
$app->get('/meetup/posts', 'PHPSW\Controller\MeetupController::postsAction')->bind('posts');
$app->get('/meetup/reviews', 'PHPSW\Controller\MeetupController::reviewsAction')->bind('reviews');
$app->get('/meetup/sponsors', 'PHPSW\Controller\MeetupController::sponsorsAction')->bind('sponsors');
$app
    ->get('/twitter/{user}-{size}', 'PHPSW\Controller\TwitterController::photoAction')
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

$app->register(new Silex\Provider\UrlGeneratorServiceProvider);

return $app;
