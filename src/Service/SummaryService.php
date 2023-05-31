<?php

namespace App\Service;

use App\Service\OpenAI\Factory\OpenAiFactoryInterface;

class SummaryService
{

    private $openAiFactory;
    private $apiResponseService;

    public function __construct(OpenAiFactoryInterface $openAiFactory, ApiResponseService $apiResponseService)
    {
        $this->openAiFactory = $openAiFactory;
        $this->apiResponseService = $apiResponseService;
    }

    public function addSummary($message, $parameters): string
    {
        $modelName = 'Gpt35Turbo';
        $aiParameter = $this->openAiFactory->createParameter($modelName);
        $aiModel = $this->openAiFactory->createModel($modelName, $aiParameter);
        $response = $this->apiResponseService->handleOpenAiFactoryResponse(
            $aiModel,
            $aiParameter,
            "ChatGPT, imagine you're a global news headline writer. Your task is to distill the essence of this message into a headline and display it. The headline should be in the same language as the message without specifying the language. If the message is too short for this task, simply display keywords and if you absolutely can't comprehend the message, display 'New Chat'. Here's your assignment:" . "'" . $message . "'",
            $parameters
        );
        if ($response['generatedMessage'][0] === '"' && $response['generatedMessage'][strlen($response['generatedMessage']) - 1] === '"') {
            // Remove double quotation marks from the first and last characters
            $response['generatedMessage'] = trim($response['generatedMessage'], '"');
        }
        return $response['generatedMessage'];
    }
}