<?php

$app = new Silex\Application;

if (strpos(__DIR__, 'phpsw.org.uk') !== false) {
    $app['env'] = 'prod';
} else {
    $app['env'] = 'dev';
}

$app['cli'] = false;

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
    $app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/' . $config . '.yml'));
}

return $app;
