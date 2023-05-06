<?php

namespace App\Service\OpenAI\Factory;

use App\Service\OpenAI\Model\ModelInterface;
use App\Service\OpenAI\Parameter\ParameterInterface;

interface OpenAiFactoryInterface
{
    public function createParameter(string $modelName): ParameterInterface;
    public function createModel(string $modelName, ParameterInterface $parameter): ModelInterface;
}