<?php

namespace Mini\Cms\Connections\Smtp;

use PHPMailer\PHPMailer\PHPMailer;

class Receiver
{
    private array $receivers;

    public function __construct(array $receivers)
    {
        foreach ($receivers as $key=>$receiver) {
            if(!empty($receiver['mail']) && !empty($receiver['name'])) {
                $this->receivers[$key] = $receiver;
            }
        }
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function addReceivers(PHPMailer &$object): void
    {
        if(empty($this->receivers)) {
            throw new \Exception("Reciever addReceivers() method can not be empty.");
        }

        foreach ($this->receivers as $receiver) {
            $object->addAddress($receiver['mail'], $receiver['name']);
        }
    }
}