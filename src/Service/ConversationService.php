<?php

namespace App\Service;

use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use DateTime;
use \Exception;

class ConversationService
{

    private $entityManager;
    private $tokenStorage;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;

        $this->user = $this->tokenStorage->getToken()->getUser();
    }

    public function createConversation($summary): Conversation
    {
        $datetime = new DateTime();

        $conversation = new Conversation();
        $conversation->setUserId($this->user);
        $conversation->setSummary($summary);
        $conversation->setDate($datetime);

        $entityManager = $this->entityManager;
        $entityManager->persist($conversation);
        $entityManager->flush();

        return $conversation;
    }

    public function getConversation(int $conversationId): ?Conversation
    {
        $conversation = $this->entityManager
            ->getRepository(Conversation::class)
            ->find($conversationId);

        if (!$conversation) {
            return null;
        }

        if ($conversation->getUserId() !== $this->user) {
            return null;
        }

        return $conversation;
    }

    public function getConversationAll(): array
    {
        $conversations = $this->entityManager
            ->getRepository(Conversation::class)
            ->findBy(['userId' => $this->user]);

        return $conversations;
    }

    /**
     * @throws Exception
     */
    public function removeConversation(Conversation $conversation)
    {
        // check if the conversation's userId is the same as the current user's
        if ($conversation->getUserId() !== $this->user) {
            throw new Exception("Cannot delete a conversation that does not belong to the current user.");
        }

        // remove the conversation from the entity manager
        $this->entityManager->remove($conversation);

        // flush the entity manager to execute the SQL DELETE statement
        $this->entityManager->flush();
    }
}