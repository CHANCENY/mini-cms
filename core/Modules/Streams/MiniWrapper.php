<?php

namespace Mini\Cms\Modules\Streams;

class MiniWrapper implements StreamWrapper
{
    protected $streamRead;

    protected $fileHandle;

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

    public function getRealPath($uri): array|string
    {
        if(str_starts_with($uri, 'public://')) {
            $uri = str_replace('public://', 'sites/default/files/public/', $uri);
        }
        if(str_starts_with($uri, 'private://')) {
            $uri = str_replace('private://', 'sites/default/files/private/', $uri);
        }
        return $uri;
    }
}