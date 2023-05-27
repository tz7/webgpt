<?php

namespace App\Service;


use App\Entity\Conversation;
use Yethee\Tiktoken\EncoderProvider;

class MemoryService
{
    public function countTokens(string $message, string $modelName = 'gpt-3.5-turbo-0301'): int {
        $provider = new EncoderProvider();
        $encoder = $provider->getForModel($modelName);
        $tokens = $encoder->encode($message);
        return count($tokens);
    }

    public function createMemory(HistoryService $historyService, Conversation $conversation, $newUserMessage, int $maxTokens): array {
        $previousChats = $historyService->getLastMeassages($conversation, 50);
        $previousChatsAsString = '';
        $currentTokens = 0;

        foreach (array_reverse($previousChats) as $message) {
            $messageTokens = $this->countTokens($message->getText());
            if ($currentTokens + $messageTokens <= $maxTokens) {
                $previousChatsAsString .= $message->getText() . "\n"; // Add each message to the string, with a newline character to separate them
                $currentTokens += $messageTokens;
            } else {
                break;
            }
        }
        $memory = "Previous Chat:" . " (" . $previousChatsAsString . ") " . $newUserMessage;
        return ['memory' => $memory, 'tokenCount' => $currentTokens];
    }
}