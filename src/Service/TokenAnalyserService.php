<?php

namespace App\Service;

use Yethee\Tiktoken\EncoderProvider;

class TokenAnalyserService
{

    private $encoderProvider;

    public function __construct(EncoderProvider $encoderProvider)
    {
        $this->encoderProvider = $encoderProvider;
    }

    public function countTokens(string $message, $modelName): int
    {
        $modelNameMap = [
            'Gpt35Turbo' => 'gpt-3.5-turbo',
            'Gpt4' => 'gpt-4',
        ];

        $modelName = $modelNameMap[$modelName] ?? $modelName;

        $encoder = $this->encoderProvider->getForModel($modelName);
        $tokens = $encoder->encode($message);
        return count($tokens);
    }
}