<?php

namespace App\Service;


use App\Entity\Conversation;
use Yethee\Tiktoken\EncoderProvider;

class MemoryService
{
    public function countTokens(string $message, string $modelName = 'gpt-3.5-turbo-0301'): int
    {
        $provider = new EncoderProvider();
        $encoder = $provider->getForModel($modelName);
        $tokens = $encoder->encode($message);
        return count($tokens);
    }

    public function createMemory(
        HistoryService $historyService,
        Conversation $conversation,
        $newUserMessage,
        int $maxTokens
    ): array {
        $previousChatsAll = $historyService->getLastMeassages($conversation, 50);

        $memory = '';
        $currentTokens = 0;

        foreach ($previousChatsAll as $message) {
            $messageText = $message->getText();
            $messageTokens = $this->countTokens($messageText);

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