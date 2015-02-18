<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../app/app.php';

$app['cli'] = false;

$app->register(new Nicl\Silex\MarkdownServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), [
    'locale_fallbacks' => array('en'),
]);

$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views'
]);

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addExtension(new PHPSW\Twig\BitlyExtension($app['bitly']['api']));
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

$app->run();
