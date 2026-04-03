<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'app_forgot_password_request')]
    public function request(
        Request $request,
        MailerInterface $mailer,
        UserRepository $userRepository,
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                try {
                    $resetToken = $this->resetPasswordHelper->generateResetToken($user);

                    $emailMessage = (new TemplatedEmail())
                        ->from(new Address('no-reply@pdffactory.local', 'Amine PDF'))
                        ->to((string) $user->getEmail())
                        ->subject('Reinitialisation de votre mot de passe')
                        ->htmlTemplate('emails/reset_password.html.twig')
                        ->context([
                            'resetToken' => $resetToken,
                        ]);

                    $mailer->send($emailMessage);

                    $this->setTokenObjectInSession($resetToken);
                } catch (ResetPasswordExceptionInterface $e) {
                    // Don't reveal whether a user exists
                }
            }

            $this->addFlash('success', 'Si un compte existe avec cet email, un lien de reinitialisation a ete envoye.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig');
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        string $token = null,
    ): Response {
        if ($token) {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('error', 'Lien de reinitialisation invalide ou expire.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if ($request->isMethod('POST')) {
            $this->resetPasswordHelper->removeResetRequest($token);

            $newPassword = (string) $request->request->get('password');
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

            $this->entityManager->flush();

            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Mot de passe modifie avec succes.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}
