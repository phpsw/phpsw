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
$app->get('/brand', 'PHPSW\Controller\AppController::brandAction')->bind('brand');
$app->get('/events', 'PHPSW\Controller\AppController::eventsAction')->bind('events');
$app->get('/events/{id}-{slug}', 'PHPSW\Controller\AppController::eventAction')->bind('event');
$app->get('/speakers', 'PHPSW\Controller\AppController::speakersAction')->bind('speakers');
$app->get('/sponsors', 'PHPSW\Controller\AppController::sponsorsAction')->bind('sponsors');
$app->get('/meetup/events', 'PHPSW\Controller\MeetupController::eventsAction')->bind('meetup_events');
$app->get('/meetup/photos', 'PHPSW\Controller\MeetupController::photosAction')->bind('meetup_photos');
$app->get('/meetup/posts', 'PHPSW\Controller\MeetupController::postsAction')->bind('meetup_posts');
$app->get('/meetup/reviews', 'PHPSW\Controller\MeetupController::reviewsAction')->bind('meetup_reviews');
$app->get('/meetup/speakers', 'PHPSW\Controller\MeetupController::speakersAction')->bind('meetup_speakers');
$app->get('/meetup/sponsors', 'PHPSW\Controller\MeetupController::sponsorsAction')->bind('meetup_sponsors');
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

$app->register(new Cocur\Slugify\Bridge\Silex\SlugifyServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

return $app;
