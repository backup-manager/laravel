<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;

/**
 * @internal
 */
trait ListsBackupFiles
{
    /**
     * @return list<FileAttributes> the files (not directories) directly in $directory
     */
    private function listBackupFiles(FilesystemOperator $filesystem, string $directory): array
    {
        $files = [];
        foreach ($filesystem->listContents($directory) as $item) {
            if ($item instanceof FileAttributes) {
                $files[] = $item;
            }
        }

        return $files;
    }

    /**
     * @param list<FileAttributes> $files
     */
    private function renderBackupFiles(array $files): void
    {
        $this->table(['Name', 'Extension', 'Size', 'Created'], array_map(
            static fn (FileAttributes $file): array => [
                basename($file->path()),
                pathinfo($file->path(), PATHINFO_EXTENSION),
                $file->fileSize() === null ? '' : self::formatBytes($file->fileSize()),
                $file->lastModified() === null ? '' : date('D j Y  H:i:s', $file->lastModified()),
            ],
            $files
        ));
    }

    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    private static function joinPath(string $directory, string $filename): string
    {
        $directory = trim($directory, '/');

        return $directory === '' ? $filename : "{$directory}/{$filename}";
    }
}
