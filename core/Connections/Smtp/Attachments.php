<?php

namespace Mini\Cms\Connections\Smtp;

use PHPMailer\PHPMailer\PHPMailer;

class Attachments
{
    private array $attachments = [];

    public function getAttachments(): array
    {
        return $this->attachments;
    }
    public function __construct(array $attachments)
    {
        foreach ($attachments as $key=>$attachment) {
            if(file_exists($attachment['path'])) {
               $this->attachments[$key]['path'] =  $attachment;
               $spl = new \SplFileInfo($attachment['path']);
               $this->attachments[$key]['type'] = $spl->getExtension();
               if(!empty($attachment['name'])) {
                   $this->attachments[$key]['name'] = $attachment['name'];
               }else {
                   $this->attachments[$key]['name'] = $spl->getFilename();
               }
            }
            else {
                unset($this->attachments[$key]);
            }
        }
    }

    public function attachFiles(PHPMailer &$object)
    {
        if(empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $object->addAttachment($attachment['path'], $attachment['name']);
            }
        }
    }
}