<?php

$app = new PHPSW\Application;

if (strpos($_SERVER['SCRIPT_NAME'], 'kahlan') !== false) {
    $app['env'] = 'testing';
} elseif (strpos(__DIR__, 'phpsw.uk') !== false) {
    $app['env'] = 'prod';
    $_SERVER['HTTPS'] = 'on';
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
$app->register(new Nicl\Silex\MarkdownServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), ['locale_fallbacks' => ['en']]);
$app->register(new Silex\Provider\TwigServiceProvider, ['twig.path' => __DIR__ . '/../views']);
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app['guzzle'] = new GuzzleHttp\Client();

$app['joindin.client'] = function () {
    return new PHPSW\Joindin\Client();
};

$app['meetup.client'] = function ($app) {
    return new PHPSW\Meetup\Client($app, $app['meetup'], $app['cli'], $app['debug']);
};

if ($app['env'] == 'prod') {
    $app['redis'] = new Predis\Client(
        [
            ['host' => 'phpsw-redis.sfkuch.ng.0001.euw1.cache.amazonaws.com', 'alias' => 'master'],
            ['host' => 'phpsw-redis-001.sfkuch.0001.euw1.cache.amazonaws.com', 'alias' => 'slave-01'],
            ['host' => 'phpsw-redis-002.sfkuch.0001.euw1.cache.amazonaws.com', 'alias' => 'slave-02'],
        ],
        ['prefix' => 'phpsw:', 'replication' => true]
    );
} else {
    $app['redis'] = new Predis\Client(null, [
        'prefix' => 'phpsw:' . $app['env'] . ':'
    ]);
}

$app['swiftmailer.options'] = [
    'host' => 'smtp.mandrillapp.com',
    'port' => '587',
    'username' => $app['email'],
    'password' => $app['mandrill']['api']['key']
];

$app['thumbor.builder'] = function ($app) {
    return Thumbor\Url\BuilderFactory::construct(
        str_replace('http', $app['env'] == 'prod' ? 'https' : 'http', $app['thumbor']['server']),
        $app['thumbor']['security_key']
    );
};

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addExtension(new PHPSW\Twig\BitlyExtension($app['bitly']['api']));
    $twig->addExtension(new PHPSW\Twig\EmojiExtension());
    $twig->addExtension(new PHPSW\Twig\SponsorExtension($app['sponsors']));
    $twig->addExtension(new PHPSW\Twig\ThumborExtension($app['thumbor.builder']));
    $twig->addExtension(new Salavert\Twig\Extension\TimeAgoExtension($app['translator']));
    $twig->addExtension(new Twig_Extensions_Extension_Text($app));
    $twig->addExtension(
        new Misd\LinkifyBundle\Twig\Extension\LinkifyTwigExtension(
            new Misd\LinkifyBundle\Helper\LinkifyHelper(
                new Misd\Linkify\Linkify()
            )
        )
    );

    return $twig;
}));

$app['twitter.client'] = function ($app) {
    return new Twitter(
        $app['twitter']['api']['key'],
        $app['twitter']['api']['secret'],
        $app['twitter']['access_token'],
        $app['twitter']['access_token_secret']
    );
};

$app['youtube.client'] = function ($app) {
    return new Madcoda\Youtube([
        'key' => $app['youtube']['api']['key']
    ]);
};

$app->get('/',                   'PHPSW\Controller\AppController::indexAction')->bind('home');
$app->get('/500',                'PHPSW\Controller\AppController::errorAction')->bind('error');
$app->get('/brand',              'PHPSW\Controller\AppController::brandAction')->bind('brand');
$app->get('/code-of-conduct',    'PHPSW\Controller\AppController::conductAction')->bind('conduct');
$app->get('/events',             'PHPSW\Controller\EventController::indexAction')->bind('events');
$app->get('/events/{id}-{slug}', 'PHPSW\Controller\EventController::showAction')->bind('event');
$app->get('/invoice/{token}',    'PHPSW\Controller\AppController::invoiceAction')->bind('invoice');
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
$app->get('/talks',              'PHPSW\Controller\TalkController::indexAction')->bind('talks');
$app->get('/talks/{slug}',       'PHPSW\Controller\TalkController::showAction')->bind('talk');
$app->get('/vouchers',           'PHPSW\Controller\AppController::vouchersAction')->bind('vouchers');

$app
    ->get('/twitter/{user}/tweets', 'PHPSW\Controller\TwitterController::tweetsAction')
    ->bind('tweets')
    ->assert('user', $app['twitter']['user'])
    ->value('user', $app['twitter']['user'])
;

$app->error(function (Exception $e, $code) use ($app) {
    if ($app['debug']) return;

    switch ($code) {
        case 404:
            $message = $app['twig']->render('error/404.html.twig', ['e' => $e]);
            break;
        default:
            $message = $app['twig']->render('error/500.html.twig', ['e' => $e]);
    }

    return new Symfony\Component\HttpFoundation\Response($message, $code);
});

return $app;
