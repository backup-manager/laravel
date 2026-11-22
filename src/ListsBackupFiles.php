<?php namespace BackupManager\Laravel;

/**
 * Lists the files in a directory of a Flysystem instance of any major version.
 *
 * Flysystem 1 returns arrays with basename/size/timestamp keys; Flysystem 2
 * and 3 return StorageAttributes objects.
 *
 * @package BackupManager\Laravel
 */
trait ListsBackupFiles {
    /**
     * @param \League\Flysystem\FilesystemInterface|\League\Flysystem\FilesystemOperator $filesystem
     * @param string|null $path
     * @return array[] each with name, extension, size (bytes or null) and timestamp (or null)
     */
    private function listBackupFiles($filesystem, $path) {
        $files = [];
        foreach ($filesystem->listContents((string) $path) as $item) {
            if ($item instanceof \League\Flysystem\StorageAttributes) {
                if ( ! $item->isFile()) continue;
                $name = basename($item->path());
                $files[] = [
                    'name' => $name,
                    'extension' => pathinfo($name, PATHINFO_EXTENSION) ?: null,
                    'size' => $item->fileSize(),
                    'timestamp' => $item->lastModified(),
                ];
                continue;
            }
            if ( ! isset($item['type']) || $item['type'] !== 'file') continue;
            $files[] = [
                'name' => $item['basename'],
                'extension' => isset($item['extension']) ? $item['extension'] : null,
                'size' => isset($item['size']) ? $item['size'] : null,
                'timestamp' => isset($item['timestamp']) ? $item['timestamp'] : null,
            ];
        }
        return $files;
    }

    /**
     * @param array[] $files from listBackupFiles()
     * @return array[] table rows: name, extension, size, created
     */
    private function backupFileRows(array $files) {
        return array_map(function ($file) {
            return [
                $file['name'],
                $file['extension'],
                $file['size'] === null ? '' : $this->formatBytes($file['size']),
                $file['timestamp'] === null ? '' : date('D j Y  H:i:s', $file['timestamp']),
            ];
        }, $files);
    }

    /**
     * @param string $directory
     * @param string $filename
     * @return string
     */
    private function joinPath($directory, $filename) {
        $directory = trim((string) $directory, '/');
        return $directory === '' ? $filename : "{$directory}/{$filename}";
    }
}
