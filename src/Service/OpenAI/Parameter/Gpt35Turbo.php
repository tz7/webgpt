<?php

namespace App\Service\OpenAI\Parameter;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Gpt35Turbo implements ParameterInterface
{

    protected $httpClient;
    protected $parameters = [];

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function createParameters($parameters)
    {
        $this->addParameter('temperature', $parameters['temperature']);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function addParameter(string $key, $value): void
    {
        $this->parameters[$key] = $value;
    }

}