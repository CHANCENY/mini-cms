<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\User;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\FileSystem\FileSizeEnum;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Mini\Cms\Modules\FileSystem\FileTypeEnum;
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

        if($this->request->isMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)) {
            $payload = $this->request->getPayload();

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
                     //TODO: sending email of verification.

                }
            }
        }

        if($theme instanceof Theme) {
            $content = $theme->view('register_form.php', []);
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write($content);
//        dump(User::load(User::create([
//            'name' => 'chance12',
//            'email' => 'chance12@gmail.com',
//            'password' => 'chance12',
//            'role' => 'admin',
//            'firstname' => 'Chance',
//            'lastname' => 'Nyasulu',
//            'active' => true,
//            'image' => $file->getUpload()['fid'] ?? null
//        ])));
    }
}