<?php

declare(strict_types=1);

namespace App\Services\Git;

use App\Enums\CloneStatus;
use App\Models\GitClone;
use App\Models\GitConnection;
use App\Models\GitRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GitCloneService
{
    private const MAX_REPO_SIZE_MB = 2048;

    /**
     * Initialize a repository clone.
     */
    public function initializeClone(
        GitRepository $repository,
        string $ref = 'main',
        string $storage = 'local'
    ): GitClone {
        $clone = GitClone::create([
            'repository_id' => $repository->id,
            'ref' => $ref,
            'storage_driver' => $storage,
            'artifact_path' => $this->generateArtifactPath($repository, $ref, $storage),
            'status' => CloneStatus::PENDING,
        ]);

        Log::info('Clone initialized', [
            'clone_id' => $clone->id,
            'repository' => $repository->full_name,
            'ref' => $ref,
            'storage' => $storage,
        ]);

        return $clone;
    }

    /**
     * Execute the clone operation.
     */
    public function executeClone(GitClone $clone, GitConnection $connection): GitClone
    {
        $startTime = microtime(true);

        try {
            $clone->markAsStarted();

            $repository = $clone->repository;
            $tempDir = $this->createTempDirectory();

            Log::info('Starting clone execution', [
                'clone_id' => $clone->id,
                'repository' => $repository->full_name,
                'ref' => $clone->ref,
                'temp_dir' => $tempDir,
            ]);

            // Get HTTPS URL with auth token
            $cloneUrl = $this->getAuthenticatedCloneUrl($repository, $connection);

            // Execute git clone
            $this->gitClone($cloneUrl, $tempDir, $clone->ref);

            // Check repository size
            $sizeBytes = $this->getDirectorySize($tempDir);
            $sizeMB = $sizeBytes / 1024 / 1024;

            if ($sizeMB > self::MAX_REPO_SIZE_MB) {
                throw new \RuntimeException(
                    "Repository size ({$sizeMB}MB) exceeds maximum allowed (".self::MAX_REPO_SIZE_MB.'MB)'
                );
            }

            // Create archive
            $archivePath = $this->createArchive($tempDir, $clone);

            // Upload to storage
            $finalPath = $this->uploadToStorage($archivePath, $clone);

            // Clean up temp directory
            $this->cleanupTempDirectory($tempDir);

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            $clone->markAsCompleted($sizeBytes, $durationMs);

            Log::info('Clone completed successfully', [
                'clone_id' => $clone->id,
                'repository' => $repository->full_name,
                'size_mb' => round($sizeMB, 2),
                'duration_ms' => $durationMs,
                'final_path' => $finalPath,
            ]);

            return $clone;
        } catch (\Exception $e) {
            Log::error('Clone failed', [
                'clone_id' => $clone->id,
                'repository' => $clone->repository->full_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $clone->markAsFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Get authenticated clone URL.
     */
    private function getAuthenticatedCloneUrl(GitRepository $repository, GitConnection $connection): string
    {
        $httpsUrl = $repository->meta['https_url'] ?? '';

        if (empty($httpsUrl)) {
            throw new \RuntimeException('Repository HTTPS URL not found');
        }

        // Insert token into URL: https://oauth2:TOKEN@github.com/owner/repo.git
        $token = $connection->getAccessToken();
        $parsedUrl = parse_url($httpsUrl);

        if ($parsedUrl === false || ! isset($parsedUrl['host'])) {
            throw new \RuntimeException('Invalid repository URL');
        }

        return sprintf(
            '%s://oauth2:%s@%s%s',
            $parsedUrl['scheme'] ?? 'https',
            $token,
            $parsedUrl['host'],
            $parsedUrl['path'] ?? ''
        );
    }

    /**
     * Execute git clone command.
     */
    private function gitClone(string $url, string $targetDir, string $ref): void
    {
        $command = sprintf(
            'git clone --depth 1 --branch %s %s %s',
            escapeshellarg($ref),
            escapeshellarg($url),
            escapeshellarg($targetDir)
        );

        $result = Process::timeout(300)->run($command);

        if (! $result->successful()) {
            throw new \RuntimeException(
                "Git clone failed: {$result->errorOutput()}"
            );
        }

        Log::debug('Git clone command executed', [
            'ref' => $ref,
            'output' => $result->output(),
        ]);
    }

    /**
     * Create temporary directory.
     */
    private function createTempDirectory(): string
    {
        $tempDir = sys_get_temp_dir().'/git_clone_'.Str::uuid();

        if (! mkdir($tempDir, 0755, true)) {
            throw new \RuntimeException("Failed to create temp directory: {$tempDir}");
        }

        return $tempDir;
    }

    /**
     * Get directory size in bytes.
     */
    private function getDirectorySize(string $dir): int
    {
        $result = Process::run("du -sb {$dir} | cut -f1");

        if (! $result->successful()) {
            throw new \RuntimeException('Failed to calculate directory size');
        }

        return (int) trim($result->output());
    }

    /**
     * Create tar.gz archive.
     */
    private function createArchive(string $sourceDir, GitClone $clone): string
    {
        $archiveName = sprintf(
            '%s_%s_%s.tar.gz',
            str_replace('/', '_', $clone->repository->full_name),
            $clone->ref,
            substr(hash('sha256', $clone->id.$clone->created_at->timestamp), 0, 8)
        );

        $archivePath = sys_get_temp_dir().'/'.$archiveName;

        $command = sprintf(
            'tar -czf %s -C %s .',
            escapeshellarg($archivePath),
            escapeshellarg($sourceDir)
        );

        $result = Process::timeout(300)->run($command);

        if (! $result->successful()) {
            throw new \RuntimeException("Archive creation failed: {$result->errorOutput()}");
        }

        Log::debug('Archive created', [
            'clone_id' => $clone->id,
            'archive_path' => $archivePath,
            'size_bytes' => filesize($archivePath),
        ]);

        return $archivePath;
    }

    /**
     * Upload archive to storage.
     */
    private function uploadToStorage(string $archivePath, GitClone $clone): string
    {
        if ($clone->storage_driver === 's3') {
            $s3Path = $clone->artifact_path;
            Storage::disk('s3')->put($s3Path, fopen($archivePath, 'r'));

            // Clean up local archive
            unlink($archivePath);

            return $s3Path;
        }

        // Local storage
        $localPath = $clone->artifact_path;
        $directory = dirname($localPath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true)) {
            throw new \RuntimeException("Failed to create storage directory: {$directory}");
        }

        if (! rename($archivePath, $localPath)) {
            throw new \RuntimeException("Failed to move archive to: {$localPath}");
        }

        return $localPath;
    }

    /**
     * Clean up temporary directory.
     */
    private function cleanupTempDirectory(string $dir): void
    {
        Process::run("rm -rf {$dir}");

        Log::debug('Temp directory cleaned up', ['dir' => $dir]);
    }

    /**
     * Generate artifact path.
     */
    private function generateArtifactPath(GitRepository $repository, string $ref, string $storage): string
    {
        $safeName = str_replace('/', '_', $repository->full_name);
        $hash = substr(hash('sha256', $repository->id.$ref.time()), 0, 8);

        if ($storage === 's3') {
            return sprintf('repos/%s/%s_%s.tar.gz', $safeName, $ref, $hash);
        }

        return sprintf(
            '/data/repos/%s/%s_%s.tar.gz',
            $safeName,
            $ref,
            $hash
        );
    }
}
