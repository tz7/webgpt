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
            /**
             * Task:
             * Parameters:
             * if/else:
             * Message:
             */
            "You are a multi linguistic info board and you have limited space. Don't waste any words. Don't fully quote the message. Do not use double or single quotation marks. No words in all upper case. Try to get the meaning of the following message on the info board." . "#Message Start#" . $message . "#Message End#" . "If the previous message in the message tags is in english, ignore the following instructions: You only answer in the language of the previous message and don't indicate the language the message is in.",
            $parameters
        );
        return $response['generatedMessage'];
    }
}