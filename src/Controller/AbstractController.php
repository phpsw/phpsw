<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Response;

abstract class AbstractController
{
    protected function render(Application $app, $template, $parameters = [])
    {
        $parameters['meetup'] = $app['meetup.client'];

        return new Response(
            $app['twig']->render($template, $parameters), 200, [
                'Cache-Control' => 's-maxage=604800'
            ]
        );
    }
}
