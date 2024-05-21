<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Connections\Smtp\MailManager;
use Mini\Cms\Connections\Smtp\Receiver;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\User;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\FileSystem\FileSizeEnum;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Mini\Cms\Modules\FileSystem\FileTypeEnum;
use Mini\Cms\Modules\Messenger\Messenger;
use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\Services\Services;
use Mini\Cms\StorageManager\Connector;
use Mini\Cms\Theme\Theme;

class UserRegistration implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        // TODO: Implement isAccessAllowed() method.
        return true;
    }

    public function writeBody(): void
    {
        $theme = Tempstore::load('theme_loaded');
        $content = null;
        $payload = $this->request->getPayload();

        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST) && !empty($payload->get('user'))) {

            if($payload->get('password') === $payload->get('confirm')) {

                $user = User::create(
                    [
                        'email' => $payload->get('email'),
                        'password' => password_hash($payload->get('password'),PASSWORD_BCRYPT),
                        'firstname' => $payload->get('firstname'),
                        'lastname' => $payload->get('lastname'),
                        'role' => $payload->get('role'),
                        'image' => trim($payload->get('image'), ','),
                        'name' => $payload->get('username'),
                    ]
                );

                if($user) {
                    $verify = new Authenticator();
                    $site = new Site();
                    $verification_url = $this->request->getSchemeAndHttpHost() . $verify->verificationToken($user);
                   $mail = MailManager::mail(
                        new Receiver([[
                            'mail' => $payload->get('email'),
                            'name' => $payload->get('firstname') . ' ' . $payload->get('lastname'),
                        ]])
                    );

                   $mail->send([
                       'subject' => 'Welcome to '. $site->getBrandingAssets('Name'),
                       'body' => '<p>Welcome to '.$site->getBrandingAssets("Name").'. Your account has been created. you are requested to activate your account.</p>
                                   <p><a href="' . $verification_url . '">Click here to activate your account</a></p>',
                   ]);
                }
            }
        }

        $roles = Services::create('config.roles')?->getRoles();
        if($theme instanceof Theme) {
            $content = $theme->view('register_form.php', ['roles' => $roles]);
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write($content);
    }
}