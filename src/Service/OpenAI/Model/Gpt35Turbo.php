<?php

namespace App\Service\OpenAI\Model;

use App\Service\OpenAI\Parameter\ParameterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Gpt35Turbo implements ModelInterface
{

    protected $httpClient;

    public function __construct(HttpClientInterface $httpClient, ParameterInterface $parameters)
    {
        $this->httpClient = $httpClient;
        $this->parameters = $parameters;
    }

    public function processRequest(string $message): array
    {
        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY']
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'temperature' => $this->parameters->getParameters()['temperature'],
            ]
        ]);

        return json_decode($response->getContent(), true);
    }
}