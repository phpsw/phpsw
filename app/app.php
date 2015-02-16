<?php

$app = new PHPSW\Application;

if (strpos($_SERVER['SCRIPT_NAME'], 'kahlan') !== false) {
    $app['env'] = 'testing';
} elseif (strpos(__DIR__, 'phpsw.org.uk') !== false) {
    $app['env'] = 'prod';
} else {
    $app['env'] = 'dev';
}

foreach (['app', $app['env'], 'secrets'] as $config) {
    if (file_exists(__DIR__ . '/../config/' . $config . '.yml')) {
        $app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/' . $config . '.yml'));
    }
}

if ($app['bugsnag']['api']['key'] && $app['env'] == 'prod') {
    $app->register(new Bugsnag\Silex\Provider\BugsnagServiceProvider, [
        'bugsnag.options' => [
            'apiKey' => $app['bugsnag']['api']['key']
        ]
    ]);
}

$app->register(new Cocur\Slugify\Bridge\Silex\SlugifyServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app['guzzle'] = new GuzzleHttp\Client();

$app['meetup.client'] = function ($app) {
    return new PHPSW\Meetup\Client($app, $app['meetup'], $app['cli'], $app['debug']);
};

$app['redis'] = new Predis\Client(null, [
    'prefix' => 'phpsw:' . ($app['env'] != 'prod' ? $app['env'] . ':' : '')
]);

$app['swiftmailer.options'] = [
    'host' => 'smtp.mandrillapp.com',
    'port' => '587',
    'username' => $app['email'],
    'password' => $app['mandrill']['api']['key']
];

$app['thumbor.builder'] = function ($app) {
    return Thumbor\Url\BuilderFactory::construct($app['thumbor']['server'], $app['thumbor']['security_key']);
};

$app['twitter.client'] = function ($app) {
    return new Twitter(
        $app['twitter']['api']['key'],
        $app['twitter']['api']['secret'],
        $app['twitter']['access_token'],
        $app['twitter']['access_token_secret']
    );
};

$app->get('/',                   'PHPSW\Controller\AppController::indexAction')->bind('home');
$app->get('/brand',              'PHPSW\Controller\AppController::brandAction')->bind('brand');
$app->get('/code-of-conduct',    'PHPSW\Controller\AppController::conductAction')->bind('conduct');
$app->get('/events',             'PHPSW\Controller\EventController::indexAction')->bind('events');
$app->get('/events/{id}-{slug}', 'PHPSW\Controller\EventController::showAction')->bind('event');
$app->get('/invoice',            'PHPSW\Controller\AppController::invoiceAction')->bind('invoice');
$app->get('/members',            'PHPSW\Controller\MemberController::indexAction')->bind('members');
$app->get('/speakers',           'PHPSW\Controller\SpeakerController::indexAction')->bind('speakers');
$app->get('/speakers/{slug}',    'PHPSW\Controller\SpeakerController::showAction')->bind('speaker');
$app->get('/sponsors',           'PHPSW\Controller\AppController::sponsorsAction')->bind('sponsors');
$app->get('/stats',              'PHPSW\Controller\EventController::statsAction')->bind('stats');
$app->get('/meetup/photos',      'PHPSW\Controller\MeetupController::photosAction')->bind('meetup_photos');
$app->get('/meetup/posts',       'PHPSW\Controller\MeetupController::postsAction')->bind('meetup_posts');
$app->get('/meetup/reviews',     'PHPSW\Controller\MeetupController::reviewsAction')->bind('meetup_reviews');
$app->get('/meetup/sponsors',    'PHPSW\Controller\MeetupController::sponsorsAction')->bind('meetup_sponsors');
$app->post('/message',           'PHPSW\Controller\MessageController::sendAction')->bind('message');
$app->get('/vouchers',           'PHPSW\Controller\AppController::vouchersAction')->bind('vouchers');

$app
    ->get('/twitter/{user}/tweets', 'PHPSW\Controller\TwitterController::tweetsAction')
    ->bind('tweets')
    ->assert('user', $app['twitter']['user'])
    ->value('user', $app['twitter']['user'])
;

return $app;
