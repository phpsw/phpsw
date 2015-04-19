<?php

namespace PHPSW\Joindin;

class Client
{
    public function get($uri, $params = [])
    {
        return json_decode(file_get_contents($uri . '?' . http_build_query(array_merge($params, [
            'format' => 'json',
            'resultsperpage' => 0,
            'verbose' => 'yes'
        ]))));
    }
}
