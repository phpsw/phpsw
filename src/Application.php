<?php

namespace PHPSW;

use Silex;

class Application extends Silex\Application
{
    use Silex\Application\SwiftmailerTrait;
    use Silex\Application\UrlGeneratorTrait;
}
