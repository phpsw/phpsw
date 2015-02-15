<?php

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

function task($command) {
    global $console;

    $input = new StringInput($command);
    $output = new NullOutput();

    ob_start();

    $console->run($input, $output);

    return ob_get_clean();
}

function trimlns($str) {
    return preg_replace('#\s*\n\s*#', "\n", trim($str));
}
