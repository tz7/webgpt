<?php

namespace App\Service\OpenAI\Factory;

use App\Service\OpenAI\Model\ModelInterface;
use App\Service\OpenAI\Parameter\ParameterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiFactory implements OpenAiFactoryInterface
{

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function createParameter(string $modelName): ParameterInterface
    {
        $className = "App\\Service\\OpenAI\\Parameter\\" . ucfirst($modelName);
        if (class_exists($className)) {
            return new $className($this->httpClient);
        }

        throw new \InvalidArgumentException(sprintf('Model "%s" does not exist.', $modelName));
    }

    public function createModel(string $modelName, ParameterInterface $parameter): ModelInterface
    {
        $className = "App\\Service\\OpenAI\\Model\\" . ucfirst($modelName);
        if (class_exists($className)) {
            return new $className($this->httpClient, $parameter);
        }

        throw new \InvalidArgumentException(sprintf('Model "%s" does not exist.', $modelName));
    }
}