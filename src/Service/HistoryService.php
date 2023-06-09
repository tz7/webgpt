<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\History;
use App\Repository\HistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class HistoryService
{

    private $entityManager;
    private $historyRepository;
    private $tokenStorage;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, HistoryRepository $historyRepository, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->historyRepository = $historyRepository;
        $this->tokenStorage = $tokenStorage;

        $this->user = $this->tokenStorage->getToken()->getUser();
    }

    public function addMessage(Conversation $conversation, $message)
    {
        $maxNumber = $this->historyRepository->findMaxNumberForConversation($conversation);

        $history = new History();
        $history->setConversationId($conversation);
        $history->setText($message);
        $history->setNumber($maxNumber + 1);

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function getHistory(Conversation $conversation): array
    {
        if ($conversation->getUserId() !== $this->user) {
            return [];
        }

        $history = $this->historyRepository->findBy(['conversationId' => $conversation]);

        return $history;
    }

    /**
     * @param Conversation $conversation
     * @param int $maxMessages (Value = available space per Token estimate)
     * @return array
     */
    public function getLastMeassages(Conversation $conversation, int $maxMessages): array
    {
        if ($conversation->getUserId() !== $this->user) {
            return [];
        }

        // Get history in descending order of id
        $history = $this->historyRepository->findBy(['conversationId' => $conversation], ['id' => 'DESC']);

        // Fetch the last N amount of messages
        $filteredHistory = array_slice($history, 0, $maxMessages);

        return $filteredHistory;
    }

    public function delete()
    {
        
    }
}