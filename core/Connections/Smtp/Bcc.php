<?php

namespace Mini\Cms\Connections\Smtp;

use PHPMailer\PHPMailer\PHPMailer;

class Bcc
{
    private array $bcc_mails;

    public function getBccMails(): array
    {
        return $this->bcc_mails;
    }
    public function __construct(array $bcc_mails)
    {
        foreach ($bcc_mails as $key=>$bcc) {
            if(!empty($bcc['mail'])) {
                $this->bcc_mails[$key] = $bcc['mail'];
            }
        }
    }

    public function addBccMail(PHPMailer $object): void
    {
        foreach ($this->bcc_mails as $bcc_mail) {
            $object->addBcc($bcc_mail);
        }
    }
}