<?php

namespace App\Controller;

use App\Service\ConversationService;
use App\Service\HistoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{

    #[Route('/api/conversation/{id}', name: 'app_api_conversation')]
    public function getConversationHistory(int $id, ConversationService $conversationService, HistoryService $historyService): Response
    {
        $conversation = $conversationService->getConversation($id);
        $messages = $historyService->getHistory($conversation);

        $formattedMessages = array_map(function ($message) {
            return [
                'content' => $message->getText(),
                'sender' => $message->getNumber() % 2 === 0 ? 'You' : 'GPT', //CHANGE 'You' : 'GPT' WITH VARIABLES ASAP
            ];
        }, $messages);

        return $this->json(['messages' => $formattedMessages]);
    }
}
