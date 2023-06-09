<?php

namespace App\Controller;

use App\Service\ApiResponseService;
use App\Service\ConversationService;
use App\Service\HistoryService;
use App\Service\MemoryService;
use App\Service\OpenAI\Factory\OpenAiFactoryInterface;
use App\Service\SummaryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ApiController extends AbstractController
{

    private $openAiFactory;
    private $apiResponseService;

    public function __construct(OpenAiFactoryInterface $openAiFactory, ApiResponseService $apiResponseService)
    {
        $this->openAiFactory = $openAiFactory;
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * @throws \Exception
     */
    #[Route('/api', name: 'app_api')]
    public function chat(Request $request, ConversationService $conversationService, HistoryService $historyService, SummaryService $summaryService, MemoryService $memoryService): Response
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'];
        $conversationId = $data['conversationId'] ?? null;

        $parameters = [
            'temperature' => $data['temperature'] ?? 1.0,
        ];

        if ($conversationId) {
            $conversation = $conversationService->getConversation($conversationId);
        } else {
            // Add Summary
            $response = $summaryService->addSummary($message, $parameters);
            // Create new Conversation with Summary
            $conversation = $conversationService->createConversation($response);
        }

        $modelName = $data['selectedModel'];
        $aiParameter = $this->openAiFactory->createParameter($modelName);
        $aiModel = $this->openAiFactory->createModel($modelName, $aiParameter);

        $messageWithMemory = $memoryService->createMemory($historyService, $conversation, $message, $modelName, 2500);

        $response = $this->apiResponseService->handleOpenAiFactoryResponse($aiModel, $aiParameter, $messageWithMemory['memory'], $parameters);

        if ($response['status'] === 'success') {

            // Add user message to history
            $historyService->addMessage($conversation, $message);

            // Add AI message to history
            $generatedMessage = $response['generatedMessage'];
            $historyService->addMessage($conversation, $generatedMessage);

            // Prepare JSON response
            return $this->json([
                'response' => $generatedMessage,
                'conversationId' => $conversation->getId(),
                'tokenCount' => $messageWithMemory['tokenCount']
            ]);
        } else {
            // Remove empty/errored conversations
            if (!$conversationId) {
                $conversationService->removeConversation($conversation);
            }
            return $this->json(['error' => $response['message']], 500);
        }
    }

    #[Route('/api/conversation/reload', name: 'app_api_conversation_reload')]
    public function conversations(ConversationService $conversationService): Response
    {
        // Get all conversations
        $conversations = $conversationService->getConversationAll();

        // Convert the conversations to a suitable format for the JSON response
        $conversationsArray = array_map(function ($conversation) {
            return [
                'id' => $conversation->getId(),
                'summary' => $conversation->getSummary()
            ];
        }, $conversations);

        // Return the conversations as a JSON response
        return $this->json($conversationsArray);
    }
}
