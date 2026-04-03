<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Entity\GenerationUserContact;
use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Repository\ToolRepository;
use App\Repository\UserContactRepository;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/convert')]
class ConvertController extends AbstractController
{
    public function __construct(
        private GotenbergService $gotenbergService,
        private EntityManagerInterface $entityManager,
        private GenerationRepository $generationRepository,
        private ToolRepository $toolRepository,
        private MailerInterface $mailer,
        private UserContactRepository $userContactRepository,
    ) {
    }

    #[Route('/{slug}', name: 'app_convert_tool')]
    public function tool(
        string $slug,
        Request $request,
    ): Response {
        $tool = $this->toolRepository->findOneBy(['slug' => $slug]);

        if (!$tool) {
            throw $this->createNotFoundException('Outil introuvable.');
        }

        /** @var User $user */
        $user = $this->getUser();
        $plan = $user->getPlan();

        // Check plan-level access
        $hasPlanAccess = $plan !== null && $plan->getTools()->contains($tool);
        if (!$hasPlanAccess) {
            $this->addFlash('error', 'Votre plan ne vous donne pas acces a cet outil. Mettez a niveau votre abonnement.');
            return $this->redirectToRoute('app_subscription');
        }

        // Check daily quota via the Voter
        $hasFullAccess = $this->isGranted('TOOL_ACCESS', $tool);

        $todayStart = new \DateTime('today');
        $todayEnd = new \DateTime('tomorrow');
        $todayCount = $this->generationRepository->countPdfGeneratedByUserOnDate(
            (int) $user->getId(),
            $todayStart,
            $todayEnd
        );

        $limit = $plan->getLimitGeneration();
        $quotaReached = !$hasFullAccess;

        // Load user contacts for sharing form
        $contacts = $this->userContactRepository->findBy(['user' => $user]);

        if ($request->isMethod('POST') && !$quotaReached) {
            try {
                $pdfContent = null;
                $filename = 'document_' . date('Y-m-d_H-i-s') . '.pdf';
                $sourceUrl = null;
                $type = $slug;

                switch ($slug) {
                    case 'url':
                        $url = (string) $request->request->get('url');
                        $sourceUrl = $url;
                        $pdfContent = $this->gotenbergService->generatePdfFromUrl($url);
                        break;

                    case 'html':
                        $htmlContent = (string) $request->request->get('html_content');
                        $pdfContent = $this->gotenbergService->generatePdfFromHtml($htmlContent);
                        break;

                    case 'markdown':
                        $mdContent = (string) $request->request->get('markdown_content');
                        $pdfContent = $this->gotenbergService->generatePdfFromHtml(
                            '<!DOCTYPE html><html><body>' . $mdContent . '</body></html>'
                        );
                        break;

                    case 'office':
                        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $file */
                        $file = $request->files->get('office_file');
                        if ($file) {
                            $pdfContent = $this->gotenbergService->generatePdfFromOffice(
                                $file->getPathname(),
                                $file->getClientOriginalName()
                            );
                            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf';
                        }
                        break;

                    case 'merge':
                        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[]|null $files */
                        $files = $request->files->all('pdf_files');
                        if ($files && is_array($files) && count($files) >= 2) {
                            $paths = [];
                            foreach ($files as $file) {
                                $paths[] = $file->getPathname();
                            }
                            $pdfContent = $this->gotenbergService->mergePdfs($paths);
                            $filename = 'merged_' . date('Y-m-d_H-i-s') . '.pdf';
                        }
                        break;

                    case 'screenshot':
                        $url = (string) $request->request->get('url');
                        $sourceUrl = $url;
                        $pdfContent = $this->gotenbergService->generateScreenshotFromUrl($url);
                        $filename = 'screenshot_' . date('Y-m-d_H-i-s') . '.png';
                        break;

                    case 'wysiwyg':
                        $htmlContent = (string) $request->request->get('wysiwyg_content');
                        $pdfContent = $this->gotenbergService->generatePdfFromHtml($htmlContent);
                        break;
                }

                if ($pdfContent) {
                    // Save PDF to disk
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pdfs';
                    $this->gotenbergService->savePdf($pdfContent, $uploadDir, $filename);

                    // Save generation record
                    $generation = new Generation();
                    $generation->setUser($user);
                    $generation->setFile($filename);
                    $generation->setType($type);
                    $generation->setSourceUrl(is_string($sourceUrl) ? $sourceUrl : null);
                    $this->entityManager->persist($generation);

                    // Share with selected contacts
                    /** @var array<string> $selectedContactIds */
                    $selectedContactIds = $request->request->all('contacts');
                    $contactEmails = [];
                    if (!empty($selectedContactIds)) {
                        foreach ($selectedContactIds as $contactId) {
                            $contact = $this->userContactRepository->find((int) $contactId);
                            if ($contact && $contact->getUser() === $user) {
                                $genContact = new GenerationUserContact();
                                $genContact->setGeneration($generation);
                                $genContact->setUserContact($contact);
                                $this->entityManager->persist($genContact);
                                $contactEmails[] = (string) $contact->getEmail();
                            }
                        }
                    }

                    $this->entityManager->flush();

                    // Send email to user
                    try {
                        $emailMessage = (new TemplatedEmail())
                            ->from(new Address('no-reply@pdffactory.local', 'Amine PDF'))
                            ->to((string) $user->getEmail())
                            ->subject('Votre fichier est pret - ' . $filename)
                            ->htmlTemplate('emails/generation_ready.html.twig')
                            ->context([
                                'generation' => $generation,
                                'user' => $user,
                            ]);
                        $this->mailer->send($emailMessage);
                    } catch (\Exception $e) {
                        // Email sending failure should not block the user
                    }

                    // Send PDF to selected contacts
                    foreach ($contactEmails as $contactEmail) {
                        try {
                            $shareEmail = (new TemplatedEmail())
                                ->from(new Address('no-reply@pdffactory.local', 'Amine PDF'))
                                ->to($contactEmail)
                                ->subject($user->getFirstname() . ' vous a partage un fichier')
                                ->htmlTemplate('emails/share_pdf.html.twig')
                                ->context([
                                    'generation' => $generation,
                                    'sender' => $user,
                                ]);
                            $this->mailer->send($shareEmail);
                        } catch (\Exception $e) {
                            // Continue even if one email fails
                        }
                    }

                    // Return file as download
                    $contentType = str_ends_with($filename, '.png')
                        ? 'image/png'
                        : 'application/pdf';

                    return new Response($pdfContent, 200, [
                        'Content-Type' => $contentType,
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                }

                $this->addFlash('error', 'Aucun contenu valide fourni.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la conversion : ' . $e->getMessage());
            }
        }

        return $this->render('convert/tool.html.twig', [
            'tool' => $tool,
            'slug' => $slug,
            'quotaReached' => $quotaReached,
            'todayCount' => $todayCount,
            'limit' => $limit,
            'contacts' => $contacts,
        ]);
    }

    /**
     * Re-download a previously generated file.
     */
    #[Route('/download/{id}', name: 'app_convert_download', priority: 10)]
    public function download(Generation $generation): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($generation->getUser() !== $user) {
            throw $this->createAccessDeniedException('Ce fichier ne vous appartient pas.');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdfs/' . $generation->getFile();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier introuvable sur le serveur.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            (string) $generation->getFile()
        );

        return $response;
    }
}
