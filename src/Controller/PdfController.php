<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Entity\User;
use App\Form\PdfGenerationType;
use App\Repository\GenerationRepository;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pdf')]
class PdfController extends AbstractController
{
    private const PDF_DIRECTORY = 'uploads/pdfs';

    #[Route('/generate', name: 'app_pdf_generate')]
    public function generate(
        Request $request,
        GotenbergService $gotenbergService,
        GenerationRepository $generationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        $plan = $user->getPlan();

        // Check if user has a plan
        if (!$plan) {
            $this->addFlash('error', 'Vous devez souscrire à un abonnement pour générer des PDFs.');
            return $this->redirectToRoute('app_subscription');
        }

        // Check daily limit (if not unlimited)
        if ($plan->getLimitGeneration() !== null) {
            $today = new \DateTime('today');
            $tomorrow = new \DateTime('tomorrow');
            $todayCount = $generationRepository->countPdfGeneratedByUserOnDate(
                $user->getId(),
                $today,
                $tomorrow
            );

            if ($todayCount >= $plan->getLimitGeneration()) {
                $this->addFlash('error', sprintf(
                    'Vous avez atteint votre limite de %d PDF(s) par jour. Passez à un plan supérieur pour générer plus de PDFs.',
                    $plan->getLimitGeneration()
                ));
                return $this->redirectToRoute('app_subscription');
            }

            $remainingGenerations = $plan->getLimitGeneration() - $todayCount;
        } else {
            $remainingGenerations = null; // Unlimited
        }

        $form = $this->createForm(PdfGenerationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $type = $data['type'];
            $pdfContent = null;
            $sourceUrl = null;

            try {
                // Check Gotenberg availability
                if (!$gotenbergService->isAvailable()) {
                    throw new \Exception('Le service de génération de PDF n\'est pas disponible. Veuillez réessayer plus tard.');
                }

                // Generate PDF based on type
                switch ($type) {
                    case 'url':
                        $url = $data['url'];
                        $sourceUrl = $url;
                        $pdfContent = $gotenbergService->generatePdfFromUrl($url);
                        break;

                    case 'file':
                        $file = $data['file'];
                        if ($file) {
                            $pdfContent = $gotenbergService->generatePdfFromFile($file);
                        }
                        break;

                    case 'wysiwyg':
                        $htmlContent = $data['html_content'];
                        $pdfContent = $gotenbergService->generatePdfFromHtml($htmlContent);
                        break;

                    default:
                        throw new \Exception('Type de génération invalide.');
                }

                if (!$pdfContent) {
                    throw new \Exception('Erreur lors de la génération du PDF.');
                }

                // Save PDF
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/' . self::PDF_DIRECTORY;
                $filename = $gotenbergService->savePdf($pdfContent, $uploadDir);

                // Save generation record
                $generation = new Generation();
                $generation->setUser($user);
                $generation->setFile($filename);
                $generation->setType($type);
                $generation->setSourceUrl($sourceUrl);

                $entityManager->persist($generation);
                $entityManager->flush();

                $this->addFlash('success', 'Votre PDF a été généré avec succès !');

                return $this->redirectToRoute('app_pdf_download', ['id' => $generation->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('pdf/generate.html.twig', [
            'form' => $form,
            'plan' => $plan,
            'remainingGenerations' => $remainingGenerations,
        ]);
    }

    #[Route('/download/{id}', name: 'app_pdf_download')]
    public function download(int $id, GenerationRepository $generationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        $generation = $generationRepository->find($id);

        if (!$generation) {
            throw $this->createNotFoundException('PDF non trouvé.');
        }

        // Check if user owns this generation
        if ($generation->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce PDF.');
        }

        $filepath = $this->getParameter('kernel.project_dir') . '/public/' . self::PDF_DIRECTORY . '/' . $generation->getFile();

        if (!file_exists($filepath)) {
            throw $this->createNotFoundException('Le fichier PDF n\'existe pas.');
        }

        return new BinaryFileResponse($filepath);
    }
}
