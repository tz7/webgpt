<?php

namespace App\Service\OpenAI\Model;

interface ModelInterface
{
    public function processRequest(string $message): array;
}