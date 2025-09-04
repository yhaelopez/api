<?php

namespace App\Twilio;

use Twilio\AuthStrategy\AuthStrategy;
use Twilio\Http\CurlClient;
use Twilio\Http\Response;

class PrismHttpClient extends CurlClient
{
    private string $mockBaseUrl;

    public function __construct(string $mockBaseUrl)
    {
        parent::__construct();
        $this->mockBaseUrl = rtrim($mockBaseUrl, '/');
    }

    public function request(string $method, string $url, array $params = [], array $data = [], array $headers = [], ?string $user = null, ?string $password = null, ?int $timeout = null, ?AuthStrategy $authStrategy = null): Response
    {
        // Replace the Twilio API base URL with our mock URL
        $mockUrl = str_replace('https://api.twilio.com', $this->mockBaseUrl, $url);

        return parent::request($method, $mockUrl, $params, $data, $headers, $user, $password, $timeout, $authStrategy);
    }
}
