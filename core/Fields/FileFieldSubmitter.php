<?php

namespace Mini\Cms\Fields;

class FileFieldSubmitter
{

    private array $fileIds;
    /**
     * @param array $keys This is keys of file fields
     */
    public function uploadFromFilesGlobal(array $keys): void
    {

        foreach ($keys as $key) {
            $file = $_FILES[$key];

            // Let's check if file is multiple
            if(!empty($file['name']) && gettype($file['name']) == 'array') {
                foreach ($file['name'] as $key => $value) {

                }
            }
        }
    }
}