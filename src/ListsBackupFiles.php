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
            static function (FileAttributes $file): array {
                $size = $file->fileSize();
                $modified = $file->lastModified();

                return [
                    basename($file->path()),
                    pathinfo($file->path(), PATHINFO_EXTENSION),
                    $size === null ? '' : self::formatBytes($size),
                    $modified === null ? '' : date('D j Y  H:i:s', $modified),
                ];
            },
            $files
        ));
    }

    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = (float) max($bytes, 0);
        $power = 0;
        while ($value >= 1024 && $power < count($units) - 1) {
            $value /= 1024;
            $power++;
        }

        return (string) round($value, 2) . ' ' . $units[$power];
    }

    private static function joinPath(string $directory, string $filename): string
    {
        $directory = trim($directory, '/');

        return $directory === '' ? $filename : "{$directory}/{$filename}";
    }
}
