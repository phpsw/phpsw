<?php

namespace PHPSW\Meetup;

use DMS\Service\Meetup\MeetupKeyAuthClient;

class Client
{
    private $client;

    public function __construct($api_key)
    {
        $this->client = MeetupKeyAuthClient::factory(['key' => $api_key]);
    }

    public function __call($method, $args)
    {
        $response = call_user_func_array([$this->client, $method], $args);

        $limit     = (int) (string) $response->getHeader('x-ratelimit-limit');
        $remaining = (int) (string) $response->getHeader('x-ratelimit-remaining');
        $reset     = (int) (string) $response->getHeader('x-ratelimit-reset');

        if ($remaining / $limit < .2) {
            sleep($reset);
        } elseif ($remaining / $limit < .5) {
            sleep($reset / 2);
        }

        return $response;
    }
}
