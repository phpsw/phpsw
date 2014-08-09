<?php

$app = new PHPSW\Application;

if (strpos(__DIR__, 'phpsw.org.uk') !== false) {
    $app['env'] = 'prod';
} else {
    $app['env'] = 'dev';
}

$app['guzzle'] = new GuzzleHttp\Client();

$app['redis'] = new Predis\Client();

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

$app->get('/',                   'PHPSW\Controller\AppController::indexAction')->bind('home');
$app->get('/brand',              'PHPSW\Controller\AppController::brandAction')->bind('brand');
$app->get('/events',             'PHPSW\Controller\EventController::indexAction')->bind('events');
$app->get('/events/{id}-{slug}', 'PHPSW\Controller\EventController::showAction')->bind('event');
$app->get('/invoice',            'PHPSW\Controller\AppController::invoiceAction')->bind('invoice');
$app->get('/speakers',           'PHPSW\Controller\SpeakerController::indexAction')->bind('speakers');
$app->get('/speakers/{slug}',    'PHPSW\Controller\SpeakerController::showAction')->bind('speaker');
$app->get('/sponsors',           'PHPSW\Controller\AppController::sponsorsAction')->bind('sponsors');
$app->get('/meetup/photos',      'PHPSW\Controller\MeetupController::photosAction')->bind('meetup_photos');
$app->get('/meetup/posts',       'PHPSW\Controller\MeetupController::postsAction')->bind('meetup_posts');
$app->get('/meetup/reviews',     'PHPSW\Controller\MeetupController::reviewsAction')->bind('meetup_reviews');
$app->get('/meetup/sponsors',    'PHPSW\Controller\MeetupController::sponsorsAction')->bind('meetup_sponsors');

$app->get('/photos/{id}/{size}.jpg', 'PHPSW\Controller\MeetupController::photoAction')
    ->bind('meetup_photo')
    ->assert('size', implode('|', ['highres', 'photo', 'thumb']))
    ->value('size', 'photo')
;

$app->get('/members/{id}/{size}.jpg', 'PHPSW\Controller\MemberController::photoAction')
    ->bind('member_photo')
    ->assert('size', implode('|', ['highres', 'photo', 'thumb']))
    ->value('size', 'photo')
;

$app->get('/speakers/{slug}/{size}.jpg', 'PHPSW\Controller\SpeakerController::photoAction')
    ->bind('speaker_photo')
    ->assert('size', implode('|', ['highres', 'photo', 'thumb']))
    ->value('size', 'photo')
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
