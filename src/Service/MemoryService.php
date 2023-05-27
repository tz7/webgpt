<?php

namespace App\Service;


use App\Entity\Conversation;
use Yethee\Tiktoken\EncoderProvider;

class MemoryService
{
    public function countTokens(string $message, $modelName): int
    {
        $modelNameMap = [
            'Gpt35Turbo' => 'gpt-3.5-turbo',
            'Gpt4' => 'gpt-4',
        ];

        $modelName = $modelNameMap[$modelName] ?? $modelName;

        $provider = new EncoderProvider();
        $encoder = $provider->getForModel($modelName);
        $tokens = $encoder->encode($message);
        return count($tokens);
    }

    public function createMemory(
        HistoryService $historyService,
        Conversation $conversation,
        $newUserMessage,
        $modelName,
        int $maxTokens
    ): array {
        $previousChatsAll = $historyService->getLastMeassages($conversation, 50);

        $memory = '';
        $currentTokens = 0;

        foreach ($previousChatsAll as $message) {
            $messageText = $message->getText();
            $messageTokens = $this->countTokens($messageText, $modelName);

            if ($currentTokens + $messageTokens > $maxTokens) {
                break;
            }

            $memory = $messageText . "\n" . $memory;
            $currentTokens += $messageTokens;
        }

        $memory = "Previous Chat: (" . $memory . ") " . $newUserMessage;

        return ['memory' => $memory, 'tokenCount' => $currentTokens];
    }
}