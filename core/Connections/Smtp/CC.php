<?php

namespace Mini\Cms\Connections\Smtp;

use PHPMailer\PHPMailer\PHPMailer;

class CC
{
    private array $cc_mails;

    public function getBccMails(): array
    {
        return $this->cc_mails;
    }
    public function __construct(array $cc_mails)
    {
        foreach ($cc_mails as $key=>$bcc) {
            if(!empty($bcc['mail'])) {
                $this->cc_mails[$key] = $bcc['mail'];
            }
        }
    }

    public function addBccMail(PHPMailer $object): void
    {
        foreach ($this->cc_mails as $ky=>$cc_mail) {
            $object->addCC($cc_mail);
        }
    }
}