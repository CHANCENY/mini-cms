<?php

namespace Mini\Cms\Modules\Streams;

class MiniWrapper implements StreamWrapper
{
    protected $streamRead;

    protected $fileHandle;
    /**
     * @var false|resource|null
     */
    private $dirHandle;
    private array $dirEntries;
    private int $dirPosition;

    public function stream_open(string $path, string $mode, int $options, string $opened_path = NULL): bool
    {
        $file_path = $this->getRealPath($path);

        if ($file_path === false) {
            // Unable to resolve the file path from the URI.
            return false;
        }

        // Check if the file exists and handle mode appropriately.
        if (!file_exists($file_path) && strpos($mode, 'r') !== false) {
            return false; // File does not exist and trying to read.
        }

        // Open the file handle based on the mode.
        $this->fileHandle = fopen($file_path, $mode);
        if ($this->fileHandle === false) {
            // Failed to open the file handle.
            return false;
        }

        return true;
    }

    public function stream_close(): void
    {
        // Imagine the stream being closed here.
    }

    public function stream_read(int $count): string
    {
        if (!is_resource($this->fileHandle)) {
            return false;
        }

        return fread($this->fileHandle, $count);
    }

    public function stream_write($data): false|int
    {
        if (!is_resource($this->fileHandle)) {
            return false;
        }

        return fwrite($this->fileHandle, $data);
    }

    public function stream_eof(): bool
    {
        // Always return true.
        return true;
    }

    function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return false;
    }

    public function stream_stat(): array
    {
        return [];
    }

    public function url_stat($uri, $flags) {
        $file_path = $this->getRealPath($uri);

        if ($file_path === false) {
            // Unable to resolve the file path from the URI.
            return false;
        }

        if (!file_exists($file_path)) {
            return false;
        }

        // Use the 'stat' function to get information about the file.
        $stat = stat($file_path);

        if ($stat === false) {
            return false;
        }

        // Return the information based on the flags.
        if ($flags & STREAM_URL_STAT_QUIET) {
            // If the flag STREAM_URL_STAT_QUIET is set, return only the mode.
            return [
                'mode' => $stat['mode'],
            ];
        } else {
            // Return the full stat information.
            return $stat;
        }
    }

    public function mkdir($uri, $mode, $options) {
        $dir_path = $this->getRealPath($uri);

        if ($dir_path === false) {
            // Unable to resolve the directory path from the URI.
            return false;
        }

        // Create the directory with the specified mode.
        if (!mkdir($dir_path, $mode, true)) {
            // Failed to create the directory.
            return false;
        }

        // Successfully created the directory.
        return true;
    }

    public function unlink($path): bool
    {
        // Translate the path
        $translatedPath = $this->getRealPath($path);
        // Check if the file exists
        if (file_exists($translatedPath)) {
            // Attempt to delete the file
            return unlink($translatedPath);
        } else {
            trigger_error("File does not exist: $translatedPath", E_USER_WARNING);
            return false;
        }
    }

    public function stream_metadata($path, $option, $value) {
        // Translate the path
        $translatedPath = $this->getRealPath($path);

        switch ($option) {
            case STREAM_META_TOUCH:
                if (is_array($value) && count($value) == 2) {
                    return touch($translatedPath, $value[0], $value[1]);
                } else {
                    return touch($translatedPath);
                }
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                return chown($translatedPath, $value);
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                return chgrp($translatedPath, $value);
            case STREAM_META_ACCESS:
                return chmod($translatedPath, $value);
            default:
                return false;
        }
    }

    public function dir_opendir($path, $options) {
        // Translate the path
        $translatedPath = $this->getRealPath($path);
        // Open the directory handle
        $this->dirHandle = opendir($translatedPath);

        if ($this->dirHandle) {
            $this->dirEntries = [];
            while (($entry = readdir($this->dirHandle)) !== false) {
                $this->dirEntries[] = $entry;
            }
            closedir($this->dirHandle);
            $this->dirPosition = 0;
            return true;
        } else {
            return false;
        }
    }

    public function dir_readdir() {
        if (isset($this->dirEntries[$this->dirPosition])) {
            return $this->dirEntries[$this->dirPosition++];
        } else {
            return false;
        }
    }

    public function dir_rewinddir() {
        $this->dirPosition = 0;
    }

    public function dir_closedir() {
        $this->dirHandle = null;
        $this->dirEntries = [];
        $this->dirPosition = 0;
        return true;
    }

    public function rmdir($path, $options) {
        // Translate the path
        $translatedPath = $this->getRealPath($path);
        // Attempt to remove the directory
        if (is_dir($translatedPath)) {
            return rmdir($translatedPath);
        } else {
            trigger_error("Directory does not exist or is not a directory: $translatedPath", E_USER_WARNING);
            return false;
        }
    }

    public function getRealPath($uri): array|string
    {
        if(str_starts_with($uri, 'public://')) {
            $uri = str_replace('public://', 'sites/default/files/public/', $uri);
        }
        if(str_starts_with($uri, 'private://')) {
            $uri = str_replace('private://', 'sites/default/files/private/', $uri);
        }
        if(str_starts_with($uri, 'module://')) {
            $uri = str_replace('module://', 'modules/', $uri);
        }
        if(str_starts_with($uri, 'theme://')) {
            $uri = str_replace('theme://', 'themes/', $uri);
        }
        return $uri;
    }
}