<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use App\Models\CustomNotification;
use App\Models\ParkingSession;
use App\Models\Transaksi;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupOldSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:old-sessions
                            {--days=7 : Number of days to keep completed sessions}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Force cleanup without confirmation}
                            {--notify : Send notifications to admin after cleanup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old completed parking sessions and transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $notify = $this->option('notify');

        $this->info("🧹 Starting cleanup of sessions older than {$days} days...");

        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No data will be deleted");
        }

        try {
            $cutoffDate = Carbon::now()->subDays($days);
            $this->info("📅 Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

            // Get statistics before cleanup
            $stats = $this->getCleanupStats($cutoffDate);

            $this->displayCleanupStats($stats);

            if ($dryRun) {
                $this->info("✅ Dry run completed. No data was deleted.");
                return 0;
            }

            // Confirm cleanup
            if (!$force && !$this->confirm("⚠️  This will permanently delete {$stats['sessions_to_delete']} sessions and {$stats['transactions_to_delete']} transactions. Continue?")) {
                $this->info("❌ Cleanup cancelled.");
                return 0;
            }

            // Perform cleanup
            $result = $this->performCleanup($cutoffDate, $stats);

            // Log cleanup activity
            LogAktivitas::logCleanup(
                "Cleaned up old parking sessions and transactions",
                [
                    'days_retained' => $days,
                    'sessions_deleted' => $result['sessions_deleted'],
                    'transactions_deleted' => $result['transactions_deleted'],
                    'space_saved' => $result['space_saved'],
                    'execution_time' => $result['execution_time'],
                ],
                LogAktivitas::LEVEL_INFO
            );

            // Send notification if requested
            if ($notify) {
                $this->sendCleanupNotification($result);
            }

            $this->info("✅ Cleanup completed successfully!");
            $this->displayCleanupResult($result);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Cleanup failed: " . $e->getMessage());

            // Log error
            LogAktivitas::logCleanup(
                "Cleanup failed: " . $e->getMessage(),
                [
                    'exception' => $e->getTraceAsString(),
                    'command_options' => $this->options(),
                ],
                LogAktivitas::LEVEL_ERROR
            );

            // Send error notification
            CustomNotification::systemAlert(
                'Cleanup Failed',
                "Automated cleanup failed: " . $e->getMessage(),
                ['error' => $e->getMessage()],
                CustomNotification::PRIORITY_CRITICAL
            );

            return 1;
        }
    }

    /**
     * Get cleanup statistics
     */
    private function getCleanupStats(Carbon $cutoffDate): array
    {
        $sessionsToDelete = ParkingSession::where('status', 'completed')
            ->where('updated_at', '<', $cutoffDate)
            ->count();

        $transactionsToDelete = Transaksi::whereIn('ticket_code', function($query) use ($cutoffDate) {
            $query->select('ticket_code')
                  ->from('parking_sessions')
                  ->where('status', 'completed')
                  ->where('updated_at', '<', $cutoffDate);
        })->count();

        $estimatedSpaceSaved = $this->estimateSpaceSaved($sessionsToDelete, $transactionsToDelete);

        return [
            'sessions_to_delete' => $sessionsToDelete,
            'transactions_to_delete' => $transactionsToDelete,
            'estimated_space_saved' => $estimatedSpaceSaved,
            'cutoff_date' => $cutoffDate,
        ];
    }

    /**
     * Display cleanup statistics
     */
    private function displayCleanupStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Sessions to delete', $stats['sessions_to_delete']],
                ['Transactions to delete', $stats['transactions_to_delete']],
                ['Estimated space saved', $this->formatBytes($stats['estimated_space_saved'])],
            ]
        );
    }

    /**
     * Perform the actual cleanup
     */
    private function performCleanup(Carbon $cutoffDate, array $stats): array
    {
        $startTime = microtime(true);
        $sessionsDeleted = 0;
        $transactionsDeleted = 0;

        $this->withProgressBar($stats['sessions_to_delete'], function() use ($cutoffDate, &$sessionsDeleted, &$transactionsDeleted) {
            // Get sessions to delete in batches
            ParkingSession::where('status', 'completed')
                ->where('updated_at', '<', $cutoffDate)
                ->chunk(100, function($sessions) use (&$sessionsDeleted, &$transactionsDeleted) {
                    $ticketCodes = $sessions->pluck('ticket_code');

                    // Delete related transactions
                    $deletedTransactions = Transaksi::whereIn('ticket_code', $ticketCodes)->delete();
                    $transactionsDeleted += $deletedTransactions;

                    // Delete sessions
                    $deletedSessions = $sessions->count();
                    $sessionsDeleted += $deletedSessions;
                });
        });

        $executionTime = microtime(true) - $startTime;
        $spaceSaved = $this->estimateSpaceSaved($sessionsDeleted, $transactionsDeleted);

        return [
            'sessions_deleted' => $sessionsDeleted,
            'transactions_deleted' => $transactionsDeleted,
            'space_saved' => $spaceSaved,
            'execution_time' => round($executionTime, 2),
        ];
    }

    /**
     * Display cleanup result
     */
    private function displayCleanupResult(array $result): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Sessions deleted', number_format($result['sessions_deleted'])],
                ['Transactions deleted', number_format($result['transactions_deleted'])],
                ['Space saved', $this->formatBytes($result['space_saved'])],
                ['Execution time', $result['execution_time'] . ' seconds'],
            ]
        );
    }

    /**
     * Estimate space saved in bytes
     */
    private function estimateSpaceSaved(int $sessions, int $transactions): int
    {
        // Rough estimation: 1KB per session, 0.5KB per transaction
        return ($sessions * 1024) + ($transactions * 512);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Send cleanup notification to admin
     */
    private function sendCleanupNotification(array $result): void
    {
        $message = "Cleanup completed: {$result['sessions_deleted']} sessions and {$result['transactions_deleted']} transactions deleted.";

        CustomNotification::cleanupReport($message, [
            'sessions_deleted' => $result['sessions_deleted'],
            'transactions_deleted' => $result['transactions_deleted'],
            'space_saved' => $this->formatBytes($result['space_saved']),
            'execution_time' => $result['execution_time'],
        ]);

        $this->info("📧 Cleanup notification sent to admin");
    }
}
