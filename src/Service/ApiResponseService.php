<?php

namespace App\Service;

use App\Service\OpenAI\Model\ModelInterface;
use App\Service\OpenAI\Parameter\ParameterInterface;

class ApiResponseService
{
    public function handleOpenAiFactoryResponse(ModelInterface $aiModel, ParameterInterface $parameterInterface, string $message, array $parameters): array
    {
        try {

            $parameterInterface->createParameters($parameters);
            $response = $aiModel->processRequest($message);
            $choices = $response['choices'] ?? [];

            if (count($choices) > 0) {
                $generatedMessage = $choices[0]['message']['content'];

                return [
                    'status' => 'success',
                    'generatedMessage' => $generatedMessage,
                    'choices' => $choices,
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'No choices found in the AI response',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
            ];
        }
    }
}