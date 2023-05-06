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
            "Get the essential meaning of the following message and describe it with 5 or preferably less keywords, but sounding like a short info board and don't cite the keywords, in the original language:" . " " . $message,
            $parameters
        );
        return $response['generatedMessage'];
    }
}