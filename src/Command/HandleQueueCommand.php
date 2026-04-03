<?php

namespace App\Command;

use App\Entity\Generation;
use App\Entity\QueueItem;
use App\Repository\QueueItemRepository;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Process queued PDF tasks (merge, etc.).
 * Designed to run via crontab every 10 minutes:
 *   * /10 * * * * php /var/www/html/bin/console app:handle-queue
 */
#[AsCommand(
    name: 'app:handle-queue',
    description: 'Process queued PDF generation/merge tasks from the queue_item table.',
)]
class HandleQueueCommand extends Command
{
    public function __construct(
        private QueueItemRepository $queueItemRepository,
        private GotenbergService $gotenbergService,
        private EntityManagerInterface $entityManager,
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Max items to process per run', '10');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getArgument('limit');
        $uploadDir = $this->projectDir . '/public/uploads/pdfs';

        $io->title('Amine PDF - Queue Handler');

        $pendingCount = $this->queueItemRepository->countPending();
        $io->info(sprintf('%d item(s) en attente. Traitement de %d max...', $pendingCount, $limit));

        if ($pendingCount === 0) {
            $io->success('Aucun element en attente.');
            return Command::SUCCESS;
        }

        $items = $this->queueItemRepository->findPendingItems($limit);
        $processed = 0;
        $errors = 0;

        foreach ($items as $item) {
            $io->text(sprintf(
                '  [#%d] type=%s, user=#%d, status=%s',
                (int) $item->getId(),
                (string) $item->getType(),
                $item->getUser() ? (int) $item->getUser()->getId() : 0,
                $item->getStatus()
            ));

            // Mark as processing
            $item->setStatus(QueueItem::STATUS_PROCESSING);
            $this->entityManager->flush();

            try {
                $pdfContent = null;
                $filename = 'queued_' . $item->getType() . '_' . date('Y-m-d_H-i-s') . '_' . $item->getId() . '.pdf';
                $payload = $item->getPayload() ?? [];

                switch ($item->getType()) {
                    case 'merge':
                        $filePaths = $payload['files'] ?? [];
                        if (count($filePaths) >= 2) {
                            // Convert stored filenames to absolute paths
                            $absolutePaths = array_map(
                                fn(string $f) => $uploadDir . '/' . $f,
                                $filePaths
                            );
                            $pdfContent = $this->gotenbergService->mergePdfs($absolutePaths);
                        }
                        break;

                    case 'url':
                        $url = $payload['url'] ?? '';
                        if ($url !== '') {
                            $pdfContent = $this->gotenbergService->generatePdfFromUrl($url);
                        }
                        break;

                    case 'html':
                        $html = $payload['html'] ?? '';
                        if ($html !== '') {
                            $pdfContent = $this->gotenbergService->generatePdfFromHtml($html);
                        }
                        break;
                }

                if ($pdfContent !== null) {
                    // Save file to disk
                    $this->gotenbergService->savePdf($pdfContent, $uploadDir, $filename);

                    // Create Generation record
                    $generation = new Generation();
                    $generation->setUser($item->getUser());
                    $generation->setFile($filename);
                    $generation->setType((string) $item->getType());
                    $this->entityManager->persist($generation);

                    $item->setResultFile($filename);
                }

                $item->setStatus(QueueItem::STATUS_DONE);
                $item->setProcessedAt(new \DateTime());
                $processed++;
                $io->text('    -> OK');
            } catch (\Exception $e) {
                $item->setStatus(QueueItem::STATUS_ERROR);
                $item->setErrorMessage($e->getMessage());
                $item->setProcessedAt(new \DateTime());
                $errors++;
                $io->error(sprintf('    -> Erreur: %s', $e->getMessage()));
            }

            $this->entityManager->flush();
        }

        $io->newLine();
        $io->success(sprintf('Termine. Traites: %d, Erreurs: %d', $processed, $errors));

        return Command::SUCCESS;
    }
}
