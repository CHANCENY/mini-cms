<?php

namespace Mini\Cms\Connections\Smtp;

use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Services\Services;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailManager
{
    private string $email_address;

    private string $password;

    private string  $username;

    private int $port;

    private string $host;

    private string $site_name;

    public function __construct(private Receiver $reciever, private Attachments|null $attachments=null, private Bcc|null $bcc = null, private CC|null $cc = null)
    {
        $site = Services::create('config.site');
        if($site instanceof Site) {
            $smtp = $site->getContactInformation('Smtp');
            if(!empty($smtp))
            {
                $this->password = $smtp['smtp_password'] ?? '';
                $this->host = $smtp['smtp_server'] ?? '';
                $this->port = (int) $smtp['smtp_port'] ?? 465;
                $this->username = $smtp['smtp_username'] ?? '';
                $this->email_address = $site->getContactInformation('Email');
                $this->site_name = $site->getBrandingAssets('Name');
            }

        }
    }

    public function send(array $params): bool
    {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $this->port ?? 465;

            //Recipients
            $mail->setFrom($this->email_address, $this->site_name);
            $this->reciever->addReceivers($mail);

           // $mail->addReplyTo('info@example.com', 'Information');
            $this->cc?->addBccMail($mail);
            $this->bcc?->addBccMail($mail);


            //Attachments
            $this->attachments?->attachFiles($mail);
            //Content
            $mail->isHTML(true);
            $mail->Subject = $params['subject'] ?? '';
            $mail->Body    = $params['body'] ?? '';
            $mail->AltBody = $params['alt_body'] ?? '';
            return $mail->send();
        } catch (\Exception $e) {
           return false;
        }
    }

    public static function mail(Receiver $reciever, Attachments|null $attachments=null, Bcc|null $bcc = null, CC|null $cc = null): MailManager
    {
        return new self($reciever, $attachments, $bcc, $cc);
    }
}