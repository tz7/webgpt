<?php

namespace App\Service\OpenAI\Parameter;

interface ParameterInterface
{
    public  function createParameters($parameters);
    public function getParameters(): array;
    public function addParameter(string $key, $value): void;
}