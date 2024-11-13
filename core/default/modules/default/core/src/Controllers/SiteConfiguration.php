<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\FileSystem\FileSizeEnum;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Mini\Cms\Modules\FileSystem\FileTypeEnum;
use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\StorageManager\Connector;
use Mini\Cms\Theme\Theme;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        if($this->request->getMethod() === 'POST' && !empty($this->request->getPayload()->get('op'))) {
            $payload = $this->request->getPayload();
            $site = new Site();
            $site->setBrandingAssets('Email', $payload->get('site_email'));
            $site->setBrandingAssets('Name', $payload->get('site_name'));
            $site->setBrandingAssets('Phone', $payload->get('site_phone'));
            $site->setContactInformation('Smtp', [
                'smtp_server' => $payload->get('smtp_server'),
                'smtp_port' => $payload->get('smtp_port') ?? 465,
                'smtp_username' => $payload->get('smtp_username'),
                'smtp_password' => $payload->get('smtp_password'),
            ]);

            if(!empty($_FILES['site_logo'])) {
                $file = new FileSystem();
                $file->connector(Connector::connect(external_connection: Database::database()));
                $file->setAllowedExtension(FileTypeEnum::FILE_TEXT);
                $file->setAllowedExtension(FileTypeEnum::FILE_PDF);
                $file->setAllowedSize(FileSizeEnum::XX_MEDIUM_FILES);
                $file->prepareUpload($_FILES['site_logo']);
                $site_logo = $file->getUpload();
            }
            else {
                $site_logo['fid'] = $payload->get('site_logo');
            }
            $site->setBrandingAssets('Logo', $site_logo);

            if($site->save()) {
                (new RedirectResponse('/user/register',StatusCode::PERMANENT_REDIRECT->value))->send();
                exit;
            }

        }
        $theme = get_global('theme_loaded');
        if($theme instanceof Theme) {
            $this->response->setStatusCode(StatusCode::OK);
            $this->response->setContentType(ContentType::TEXT_HTML);
            $this->response->write($theme->view('site_configurations.php'));
        }
    }
}