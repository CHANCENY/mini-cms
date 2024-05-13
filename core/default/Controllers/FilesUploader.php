<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ContentTypeEnum;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\FileSystem\FileSizeEnum;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Mini\Cms\Modules\FileSystem\FileTypeEnum;
use Mini\Cms\StorageManager\Connector;

class FilesUploader implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        // TODO: Implement writeBody() method.
        $this->response->setContentType(ContentTypeEnum::APPLICATION_JSON);

        // Lets uploaded files
        $config = new ConfigFactory();
        $file_uploader = $config->get('file_auto_uploader');
        if(!empty($file_uploader['is_active'])) {
            $file = new FileSystem();
            $file->setSaveAs(!empty($file_uploader['is_public']));
            $file->connector(Connector::connect(external_connection: Database::database()));

            // Set size.
            if(FileSizeEnum::tryFrom($file_uploader['file_size'])) {
                $file->setAllowedSize(FileSizeEnum::tryFrom($file_uploader['file_size']));
            }

            // Setting types.
            foreach ($file_uploader['file_type'] as $item) {
                if(FileTypeEnum::tryFrom($item)) {
                    $file->setAllowedExtension(FileTypeEnum::tryFrom($item));
                }
            }

            // uploading file.
            $file->prepareUpload($_FILES['files']);
            $file->save();

            // Fids
            $fids = $file->getUpload();

            $response_data = [];
            foreach($fids as $key=>$fid) {
                $files = File::load($fid['fid']);
                $response_data[] = [
                    'link' => $files->getFilePath(),
                    'id' => $fid['fid'],
                    'name' => $files->getName(),
                ];
            }

            $this->response->write($response_data);
        }

    }
}