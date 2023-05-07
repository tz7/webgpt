<?php

namespace App\Service;


use App\Entity\Conversation;

class MemoryService
{
    public function createMemory(HistoryService $historyService, Conversation $conversation, $newUserMessage): string
    {
        $previousChats = $historyService->getLastMeassages($conversation, 18);

        $previousChatsAsString = '';

        foreach ($previousChats as $message) {
            $previousChatsAsString .= $message->getText() . "\n"; // Add each message to the string, with a newline character to separate them
        }

        return "Previous Chat:" . " (" . $previousChatsAsString . ") " . $newUserMessage;
    }
}