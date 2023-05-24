<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        foreach ($users as $user) {
            $user->setPassword('');
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $user->setPassword('');

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Store the original password
        $originalPassword = $user->getPassword();

        // Clear the password before creating the form
        $user->setPassword('');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the password field is empty
            if (empty($user->getPassword())) {
                // If the password field is empty, keep the original password
                $user->setPassword($originalPassword);
            } else {
                // If the password field has a new value, hash it and store it
                $password = $userPasswordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * TODO not yet implemented
     * User Request of change password
     */

    #[Route('/{id}/change-password', name: 'app_user_change_password', methods: ['POST'])]
    public function changePassword(Request $request, User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Fetch the password from request data
        $password = $request->request->get('password');

        // Here you can add server-side validation for the password
        // If the password is not valid, return an error response
        if (!$this->isPasswordValid($password)) {
            return new JsonResponse(['status' => 'error', 'errors' => 'Invalid password'], Response::HTTP_BAD_REQUEST);
        }

        // Hash the new password and set it
        $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Persist and flush the User entity
        $entityManager->persist($user);
        $entityManager->flush();

        // Return a successful JSON response
        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }

    private function isPasswordValid(string $password): bool
    {
        // Here, implement your password validation logic
        // The example assumes a minimum length of 8 characters
        return strlen($password) >= 8;
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
