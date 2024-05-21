<?php

namespace Mini\Cms\default\Controllers;

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
            $site->setContactInformation('Email', $payload->get('site_email'));
            $site->setContactInformation('Name', $payload->get('site_name'));
            $site->setContactInformation('Smtp', [
                'smtp_server' => $payload->get('smtp_server'),
                'smtp_port' => $payload->get('smtp_port') ?? 465,
                'smtp_username' => $payload->get('smtp_user'),
                'smtp_password' => $payload->get('smtp_password'),
            ]);

            if(!empty($_FILES['site_privacy'])){
                $file = new FileSystem();
                $file->connector(Connector::connect(external_connection: Database::database()));
                $file->setAllowedExtension(FileTypeEnum::FILE_TEXT);
                $file->setAllowedExtension(FileTypeEnum::FILE_PDF);
                $file->setAllowedSize(FileSizeEnum::XX_MEDIUM_FILES);
                $file->prepareUpload($_FILES['site_privacy']);
                $file->save();
                $site_privacy = $file->getUpload();
            }
            else{
                $site_privacy['fid'] = trim($payload->get('site_privacy'),',' );
            }

            if(!empty($_FILES['site_terms'])) {
                $file = new FileSystem();
                $file->connector(Connector::connect(external_connection: Database::database()));
                $file->setAllowedExtension(FileTypeEnum::FILE_TEXT);
                $file->setAllowedExtension(FileTypeEnum::FILE_PDF);
                $file->setAllowedSize(FileSizeEnum::XX_MEDIUM_FILES);
                $file->prepareUpload($_FILES['site_terms']);
                $file->save();
                $site_terms = $file->getUpload();
            }
            else {
                $site_terms['fid'] = trim($payload->get('site_terms'), ',');
            }

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

            $site->setLegalInformation('PrivacyPolicy', $site_privacy);
            $site->setLegalInformation('TermsOfService', $site_terms);

            $site->setBrandingAssets('Logo', $site_logo);
            $site->setBrandingAssets('Name', $payload->get('site_name'));
            $site->setBrandingAssets('Slogan', $payload->get('site_slogan'));

            $site->setDomain($payload->get('domain_name'));
            $site->setPurpose($payload->get('purpose'));

            $site->setSocial([
                'Facebook' => $payload->get('site_facebook'),
                'Instagram' => $payload->get('site_instagram'),
                'Twitter' => $payload->get('site_twitter'),
                'LinkedIn' => $payload->get('LinkedIn'),
                'WhatsApp' => $payload->get('site_whatsapp'),
            ]
            );
            if($site->save()) {
                (new RedirectResponse('/user/register',StatusCode::PERMANENT_REDIRECT->value))->send();
                exit;
            }

        }
        $theme = Tempstore::load('theme_loaded');
        if($theme instanceof Theme) {
            $this->response->setStatusCode(StatusCode::OK);
            $this->response->setContentType(ContentType::TEXT_HTML);
            $this->response->write($theme->view('site_configurations.php'));
        }
    }
}