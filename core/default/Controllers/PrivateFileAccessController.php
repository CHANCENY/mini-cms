<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\FileSystem\File;

class PrivateFileAccessController extends File implements ControllerInterface
{

    private File|null $file;

    public function __construct(private Request &$request, private Response &$response)
    {
        parent::__construct();

        if($this->request->get('fid')) {
           $this->file = $this->file($this->request->get('fid',0));
        }
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
        if($this->file instanceof File) {
            $current_user = new CurrentUser();
            if(((int) $current_user->id()) !== $this->file->getOwner()) {
                $this->response->setContentType(ContentType::IMAGE_PNG);
                $this->response->write($this->defaultImage());
                return;
            }
        }
        if($this->file instanceof File) {
            $contentType = ContentType::fromMimeType($this->file->fetType());
            $file_name = $this->getPrivateFilename($this->request->get('style'));
            if($contentType) {
                $this->response->setContentType($contentType)
                    ->write(file_get_contents($file_name));
            }
        }
        else {
            $this->response->setContentType(ContentType::IMAGE_PNG);
            $this->response->write($this->defaultImage());
        }
    }

    public function defaultImage(): string
    {
        return file_get_contents(__DIR__ .'/default_image.png');
    }
}