<?php

namespace Mini\Cms\default\modules\default\inbox\src\Controllers;

use Mini\Cms\Connections\Smtp\MailManager;
use Mini\Cms\Connections\Smtp\Receiver;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Mini;
use Mini\Cms\Services\Services;

class ImapPanel implements ControllerInterface
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
        $imap = Services::create('imap.reader');
        if($this->request->isMethod('POST')) {
            $data = $this->request->getPayload();

            if(!empty($data->get('to_email')) && filter_var($data->get('to_email'), FILTER_VALIDATE_EMAIL)) {

                $receiver = new Receiver([
                    [
                        'name' => $data->get('to_name'),
                        'mail' => $data->get('to_email')
                    ]
                ]);

                $params['subject'] = $data->get('subject');
                $params['message'] = $data->get('message');
                if(MailManager::mail($receiver)->send($params)) {
                    Mini::messenger()->addMessage("Email is successfully sent");
                }else {
                    Mini::messenger()->addMessage("Email is not sent");
                }
            }
            else {
                Mini::messenger()->addErrorMessage("To email address is not valid");
            }
        }
        $this->response->write(
            Services::create('render')
            ->render('imap-panel-view.php',[
                'inbox'=> $imap->readInbox(),
            ])
        );
    }
}