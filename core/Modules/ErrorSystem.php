<?php

namespace Mini\Cms\Modules;

use Mini\Cms\Configurations\ConfigFactory;

class ErrorSystem
{
    private string $error_storage = '../configs/errors';

    private array $savable_error;

    public function __construct()
    {
        $config = new ConfigFactory();
    }

    public function save(): bool
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

    public function setException(\Throwable|\Exception|\PDOException $exception): void
    {
        $this->savable_error = [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'trace' => $exception->getTraceAsString(),
            'report_on' => time(),
            'type' => 'Exception'
        ];
    }

    public function setError($errno, $errstr, $errfile, $errline)
    {
        $this->savable_error = [
            'message' => $errstr,
            'line' => $errline,
            'code' => $errno,
            'file' => $errfile,
            'trace' => $errfile,
            'report_on' => time(),
            'type' => 'Php Error'
        ];
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