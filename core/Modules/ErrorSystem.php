<?php

namespace Mini\Cms\Modules;

use Mini\Cms\Configurations\ConfigFactory;

class ErrorSystem
{
    private \Throwable|\Exception|\PDOException $error;

    private string $error_storage = '../configs/errors';

    private bool $handle_errors = false;

    private array $savable_error;

    public function __construct()
    {
        $config = new ConfigFactory();
        $this->handle_errors = $config->get('error_saver')['is_active'] ?? false;
    }

    public function error(): bool
    {
        if ($this->handle_errors) {
            $this->savable_error = [
                'message' => $this->error->getMessage(),
                'line' => $this->error->getLine(),
                'code' => $this->error->getCode(),
                'file' => $this->error->getFile(),
                'trace' => $this->error->getTraceAsString(),
                'report_on' => time(),
                'type' => 'critical'
            ];
            return $this->save();
        }
        return false;
    }

    public function warning(): bool
    {
        if ($this->handle_errors) {
            $this->savable_error = [
                'message' => $this->error->getMessage(),
                'line' => $this->error->getLine(),
                'code' => $this->error->getCode(),
                'file' => $this->error->getFile(),
                'trace' => $this->error->getTraceAsString(),
                'report_on' => time(),
                'type' => 'warning'
            ];
            return $this->save();
        }
        return false;
    }

    public function info(): bool
    {
        if ($this->handle_errors) {
            $this->savable_error = [
                'message' => $this->error->getMessage(),
                'line' => $this->error->getLine(),
                'code' => $this->error->getCode(),
                'file' => $this->error->getFile(),
                'trace' => $this->error->getTraceAsString(),
                'report_on' => time(),
                'type' => 'info'
            ];
            return $this->save();
        }
        return false;
    }

    private function save(): bool
    {
       $path = $this->error_storage .'/'. $this->savable_error['report_on']. '.txt';
       return !empty(file_put_contents($path, json_encode($this->savable_error, JSON_PRETTY_PRINT)));
    }

    public function getErrors(): array
    {
        if (!is_dir($this->error_storage)) {
            @mkdir($this->error_storage, 0777, true);
            return [];
        }

        // Get the list of error files
        $files = array_diff(scandir($this->error_storage), ['.', '..']);

        // Sort the files by creation time (oldest to newest)
        usort($files, function ($a, $b) {
            return filectime($this->error_storage . '/' . $a) - filectime($this->error_storage . '/' . $b);
        });

        // Map the sorted files to their content (decoding the JSON)
        $errors = array_map(function ($error) {
            return json_decode(file_get_contents($this->error_storage . '/' . $error, true));
        }, $files);

        // Sort the errors array by 'report_on' key in descending order
        usort($errors, function ($a, $b) {
            return $b->report_on - $a->report_on;  // DESC sorting
        });

        return $errors;
    }

    public function getError(string $error_id): \stdClass|null
    {
        $error = array_filter($this->getErrors(),function ($error) use ($error_id){
            return $error->report_on == $error_id;
        });

        if($error) {
            return reset($error);
        }
        return null;
    }

    public function isOn(): bool
    {
        return $this->handle_errors;
    }

    public function setException(\Throwable|\Exception|\PDOException $exception): void
    {
       $this->error = $exception;
    }

    public function clear(): true
    {
        if (!is_dir($this->error_storage)) {
            @mkdir($this->error_storage, 0777, true);
        }
        foreach (array_diff(scandir($this->error_storage), ['.','..']) as $file) {
            unlink($this->error_storage . '/' . $file);
        }
        return true;
    }
}