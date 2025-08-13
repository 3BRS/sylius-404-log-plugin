<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ThreeBRS\Sylius404LogPlugin\Repository\NotFoundLogRepositoryInterface;

#[AsCommand(
    name: 'three-brs:404-logs:cleanup',
    description: 'Delete 404 logs older than specified number of days',
)]
class CleanupNotFoundLogsCommand extends Command
{
    public function __construct(
        private NotFoundLogRepositoryInterface $notFoundLogRepository,
        private EntityManagerInterface $entityManager,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('days', InputArgument::REQUIRED, 'Number of days to keep logs (older logs will be deleted)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be deleted without actually deleting')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Number of records to delete in each batch', 1000)
            ->setHelp(
                'This command deletes 404 logs older than the specified number of days.' . \PHP_EOL .
                'Example: php bin/console three-brs:404-logs:cleanup 30' . \PHP_EOL .
                'This will delete all logs older than 30 days.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $days = (int) $input->getArgument('days');
        $isDryRun = $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');

        if ($days <= 0) {
            $io->error('Number of days must be greater than 0');

            return Command::FAILURE;
        }

        $cutoffDate = new \DateTime();
        $cutoffDate->modify('-' . $days . ' days');

        $io->title('404 Logs Cleanup');
        $io->info(sprintf('Cleaning up logs older than %d days (before %s)', $days, $cutoffDate->format('Y-m-d H:i:s')));

        if ($isDryRun) {
            $io->warning('DRY RUN MODE - No logs will be actually deleted');
        }

        // Count total logs to be deleted
        $totalCount = $this->notFoundLogRepository->countLogsOlderThan($cutoffDate);

        if ($totalCount === 0) {
            $io->success('No logs found to delete');

            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d logs to delete', $totalCount));

        if ($isDryRun) {
            $io->success(sprintf('DRY RUN: Would delete %d logs older than %s', $totalCount, $cutoffDate->format('Y-m-d H:i:s')));

            return Command::SUCCESS;
        }

        // Delete in batches
        $deletedTotal = 0;
        $progressBar = $io->createProgressBar($totalCount);
        $progressBar->start();

        while (true) {
            $deletedBatch = $this->notFoundLogRepository->deleteLogsOlderThanInBatch($cutoffDate, $batchSize);

            if ($deletedBatch === 0) {
                break;
            }

            $deletedTotal += $deletedBatch;
            $progressBar->advance($deletedBatch);

            // Clear entity manager to prevent memory issues
            $this->entityManager->clear();
        }

        $progressBar->finish();
        $io->newLine();

        $io->success(sprintf('Successfully deleted %d logs older than %s', $deletedTotal, $cutoffDate->format('Y-m-d H:i:s')));

        return Command::SUCCESS;
    }
}
