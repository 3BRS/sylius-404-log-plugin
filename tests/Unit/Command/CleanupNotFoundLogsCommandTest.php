<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Unit\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use ThreeBRS\Sylius404LogPlugin\Command\CleanupNotFoundLogsCommand;
use ThreeBRS\Sylius404LogPlugin\Repository\NotFoundLogRepositoryInterface;

class CleanupNotFoundLogsCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private MockObject|NotFoundLogRepositoryInterface $mockRepository;
    private MockObject|EntityManagerInterface $mockEntityManager;
    private CleanupNotFoundLogsCommand $command;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(NotFoundLogRepositoryInterface::class);
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);

        $this->command = new CleanupNotFoundLogsCommand(
            $this->mockRepository,
            $this->mockEntityManager
        );

        $application = new Application();
        $application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertSame('three-brs:404-logs:cleanup', $this->command->getName());
    }

    public function testCommandHasCorrectDescription(): void
    {
        $this->assertSame('Delete 404 logs older than specified number of days', $this->command->getDescription());
    }

    public function testExecuteWithInvalidDaysArgument(): void
    {
        $this->commandTester->execute(['days' => '0']);

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Number of days must be greater than 0', $this->commandTester->getDisplay());
    }

    public function testExecuteWithNegativeDaysArgument(): void
    {
        $this->commandTester->execute(['days' => '-5']);

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Number of days must be greater than 0', $this->commandTester->getDisplay());
    }

    public function testExecuteWithNoLogsToDelete(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('countLogsOlderThan')
            ->willReturn(0);

        $this->commandTester->execute(['days' => '30']);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('No logs found to delete', $this->commandTester->getDisplay());
    }

    public function testExecuteWithDryRunMode(): void
    {
        $logsCount = 150;

        $this->mockRepository
            ->expects($this->once())
            ->method('countLogsOlderThan')
            ->willReturn($logsCount);

        $this->commandTester->execute([
            'days' => '30',
            '--dry-run' => true
        ]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('DRY RUN MODE - No logs will be actually deleted', $output);
        $this->assertStringContainsString("Found {$logsCount} logs to delete", $output);
        $this->assertStringContainsString("DRY RUN: Would delete {$logsCount} logs older than", $output);
    }

    public function testExecuteWithActualDeletion(): void
    {
        $totalLogs = 100;
        $batchSize = 50;

        $this->mockRepository
            ->expects($this->once())
            ->method('countLogsOlderThan')
            ->willReturn($totalLogs);

        // First batch deletes 50, second batch deletes 50, third batch returns 0 (no more logs)
        $this->mockRepository
            ->expects($this->exactly(3))
            ->method('deleteLogsOlderThanInBatch')
            ->willReturnOnConsecutiveCalls(50, 50, 0);

        // Entity manager should be cleared after each batch
        $this->mockEntityManager
            ->expects($this->exactly(2))
            ->method('clear');

        $this->commandTester->execute([
            'days' => '7',
            '--batch-size' => $batchSize
        ]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString("Found {$totalLogs} logs to delete", $output);
        $this->assertStringContainsString("Successfully deleted {$totalLogs} logs older than", $output);
    }

    public function testExecuteWithCustomBatchSize(): void
    {
        $totalLogs = 250;
        $customBatchSize = 100;

        $this->mockRepository
            ->expects($this->once())
            ->method('countLogsOlderThan')
            ->willReturn($totalLogs);

        // Three batches: 100, 100, 50, then 0
        $this->mockRepository
            ->expects($this->exactly(4))
            ->method('deleteLogsOlderThanInBatch')
            ->with(
                $this->isInstanceOf(\DateTimeInterface::class),
                $customBatchSize
            )
            ->willReturnOnConsecutiveCalls(100, 100, 50, 0);

        $this->mockEntityManager
            ->expects($this->exactly(3))
            ->method('clear');

        $this->commandTester->execute([
            'days' => '14',
            '--batch-size' => $customBatchSize
        ]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString("Successfully deleted {$totalLogs} logs older than", $this->commandTester->getDisplay());
    }

    public function testExecuteCalculatesCorrectCutoffDate(): void
    {
        $days = 30;
        $expectedCutoffDate = new \DateTime();
        $expectedCutoffDate->modify("-{$days} days");

        $this->mockRepository
            ->expects($this->once())
            ->method('countLogsOlderThan')
            ->with($this->callback(function (\DateTimeInterface $date) use ($expectedCutoffDate) {
                // Allow 1 second tolerance for execution time
                return abs($date->getTimestamp() - $expectedCutoffDate->getTimestamp()) <= 1;
            }))
            ->willReturn(0);

        $this->commandTester->execute(['days' => (string) $days]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteDisplaysCorrectMessages(): void
    {
        $days = 15;
        $logsCount = 75;

        $this->mockRepository
            ->method('countLogsOlderThan')
            ->willReturn($logsCount);

        $this->mockRepository
            ->method('deleteLogsOlderThanInBatch')
            ->willReturnOnConsecutiveCalls($logsCount, 0);

        $this->commandTester->execute(['days' => (string) $days]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('404 Logs Cleanup', $output);
        $this->assertStringContainsString("Cleaning up logs older than {$days} days", $output);
        $this->assertStringContainsString("Found {$logsCount} logs to delete", $output);
        $this->assertStringContainsString("Successfully deleted {$logsCount} logs", $output);
    }

    public function testExecuteWithZeroBatchDeleteReturnsImmediately(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('countLogsOlderThan')
            ->willReturn(50);

        // First call returns 0, so loop should exit immediately
        $this->mockRepository
            ->expects($this->once())
            ->method('deleteLogsOlderThanInBatch')
            ->willReturn(0);

        // Entity manager should not be called since no logs were deleted
        $this->mockEntityManager
            ->expects($this->never())
            ->method('clear');

        $this->commandTester->execute(['days' => '7']);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Successfully deleted 0 logs', $this->commandTester->getDisplay());
    }

    public function testDefaultBatchSizeIs1000(): void
    {
        $this->mockRepository
            ->method('countLogsOlderThan')
            ->willReturn(1);

        $this->mockRepository
            ->expects($this->exactly(2))
            ->method('deleteLogsOlderThanInBatch')
            ->withConsecutive(
                [$this->isInstanceOf(\DateTimeInterface::class), 1000],
                [$this->isInstanceOf(\DateTimeInterface::class), 1000]
            )
            ->willReturnOnConsecutiveCalls(1, 0);

        $this->commandTester->execute(['days' => '1']);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
    }
}
