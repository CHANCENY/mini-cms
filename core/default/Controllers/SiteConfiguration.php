<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ContentTypeEnum;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCodeEnum;
use Mini\Cms\Modules\FileSystem\FileSizeEnum;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Mini\Cms\Modules\FileSystem\FileTypeEnum;
use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\StorageManager\Connector;
use Mini\Cms\Theme\Theme;

class SiteConfiguration implements ControllerInterface
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
        if($this->request->getMethod() === 'POST') {
            $payload = $this->request->getPayload();
            $site = new Site();
            $site->setContactInformation('Email', $payload->get('site_email'));
            $site->setContactInformation('Name', $payload->get('site_name'));
            $site->setContactInformation('Smtp', [
                'smtp_server' => $payload->get('smtp_server'),
                'smtp_port' => $payload->get('smtp_port') ?? 465,
                'smtp_username' => $payload->get('smtp_user'),
                'smtp_password' => $payload->get('smtp_password'),
            ]);

            $file = new FileSystem();
            $file->connector(Connector::connect(external_connection: Database::database()));
            $file->setAllowedExtension(FileTypeEnum::FILE_TEXT);
            $file->setAllowedExtension(FileTypeEnum::FILE_PDF);
            $file->setAllowedSize(FileSizeEnum::XX_MEDIUM_FILES);
            $file->prepareUpload($_FILES['site_privacy']);
            $site_privacy = $file->save();
            dump($site_privacy);

        }
        $theme = Tempstore::load('theme_loaded');
        if($theme instanceof Theme) {
            $this->response->setStatusCode(StatusCodeEnum::OK);
            $this->response->setContentType(ContentTypeEnum::TEXT_HTML);
            $this->response->write($theme->view('site_configurations.php'));
        }
    }
}