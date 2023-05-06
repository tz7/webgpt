<?php

namespace App\Controller;

use App\Service\ConversationService;
use App\Service\HistoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ApiController extends AbstractController
{

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/api', name: 'app_api')]
    public function chat(Request $request, ConversationService $conversationService, HistoryService $historyService): Response
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'];
        $conversationId = $data['conversationId'] ?? null;

        if ($conversationId) {
            $conversation = $conversationService->getConversation($conversationId);
        } else {
            $conversation = $conversationService->createConversation();
        }

        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY']
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'temperature' => 0.7,
            ]
        ]);

        $content = $response->toArray();

        //Add user Message to History
        $historyService->addMessage($conversation, $message);

        //Add Ai Message to History
        $generatedTokens = $content['choices'][0]['message'];
        $generatedMessageAsText = implode(' ', $generatedTokens);
        // Remove "assistant" prefix from message
        $generatedMessageAsText = preg_replace('/^assistant\s+/i', '', $generatedMessageAsText);
        $historyService->addMessage($conversation, $generatedMessageAsText);

        //Prepare json response
        $generatedMessage = $content['choices'][0]['message']['content'];

        return $this->json(['response' => $generatedMessage]);
    }
}
