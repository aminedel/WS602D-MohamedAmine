<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserContact;
use App\Repository\UserContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/contacts')]
class ContactController extends AbstractController
{
    #[Route('/', name: 'app_contacts')]
    public function index(UserContactRepository $contactRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $contacts = $contactRepository->findBy(
            ['user' => $user],
            ['lastname' => 'ASC']
        );

        return $this->render('contacts/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    #[Route('/add', name: 'app_contacts_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            /** @var User $user */
            $user = $this->getUser();

            $contact = new UserContact();
            $contact->setUser($user);
            $contact->setLastname((string) $request->request->get('lastname'));
            $contact->setFirstname((string) $request->request->get('firstname'));
            $contact->setEmail((string) $request->request->get('email'));

            $em->persist($contact);
            $em->flush();

            $this->addFlash('success', 'Contact ajouté avec succès.');
            return $this->redirectToRoute('app_contacts');
        }

        return $this->render('contacts/add.html.twig');
    }

    #[Route('/edit/{id}', name: 'app_contacts_edit', methods: ['GET', 'POST'])]
    public function edit(
        UserContact $contact,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($contact->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $contact->setLastname((string) $request->request->get('lastname'));
            $contact->setFirstname((string) $request->request->get('firstname'));
            $contact->setEmail((string) $request->request->get('email'));
            $em->flush();

            $this->addFlash('success', 'Contact modifié avec succès.');
            return $this->redirectToRoute('app_contacts');
        }

        return $this->render('contacts/edit.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_contacts_delete', methods: ['POST'])]
    public function delete(
        UserContact $contact,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($contact->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($contact);
        $em->flush();

        $this->addFlash('success', 'Contact supprimé avec succès.');
        return $this->redirectToRoute('app_contacts');
    }
}
