<?php

namespace PHPSW\Meetup\API;

use DMS\Service\Meetup\MeetupKeyAuthClient;
use Guzzle\Http\Exception\ClientErrorResponseException;

class Client
{
    private $client;

    public function __construct($api_key)
    {
        $this->client = MeetupKeyAuthClient::factory(['key' => $api_key]);
    }

    public function __call($method, $args)
    {
        do {

            try {
                $response = call_user_func_array([$this->client, $method], $args);
            } catch (ClientErrorResponseException $e) {
                $response = $e->getResponse();

                if ($response->getStatusCode() != 429) throw $e;
            }

            $limit     = (int) (string) $response->getHeader('x-ratelimit-limit');
            $remaining = (int) (string) $response->getHeader('x-ratelimit-remaining');
            $reset     = (int) (string) $response->getHeader('x-ratelimit-reset');

            if ($remaining / $limit < .2) {
                sleep($reset);
            } elseif ($remaining / $limit < .5) {
                sleep($reset / 2);
            }

        } while ($response->getStatusCode() == 429);

        return $response;
    }
}
