<?php

namespace App\Service;


use App\Entity\Conversation;
use Yethee\Tiktoken\EncoderProvider;

class MemoryService
{

    private $tokenAnalyserService;

    public function __construct(TokenAnalyserService $tokenAnalyserService)
    {
        $this->tokenAnalyserService = $tokenAnalyserService;
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
            $messageTokens = $this->tokenAnalyserService->countTokens($messageText, $modelName);

            if ($currentTokens + $messageTokens > $maxTokens) {
                break;
            }

            $memory = $messageText . "\n" . $memory;
            $currentTokens += $messageTokens;
        }

        if (!empty(trim($memory))) {
            $memory = "Previous Chat: (" . $memory . ") ";
        }

        $memory .= $newUserMessage;

        return ['memory' => $memory, 'tokenCount' => $currentTokens];
    }
}