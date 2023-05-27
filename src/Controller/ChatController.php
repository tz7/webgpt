<?php

namespace App\Controller;

use App\Service\ConversationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat')]
    public function index(ConversationService $conversationService): Response
    {

        $conversations = $conversationService->getConversationAll();

        return $this->render('chat/index.html.twig', [
            'controller_name' => 'ChatController',
            'conversations' => $conversations,
        ]);
    }
}
