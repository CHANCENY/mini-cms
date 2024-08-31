<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\Authentication\Authentication;
use Mini\Cms\Modules\Authentication\AuthenticationInterface;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\ErrorSystem;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\Route;
use Mini\Cms\Theme\Theme;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Login implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    public function isAccessAllowed(): bool
    {
        return true;
    }

    public function writeBody(): void
    {
        $theme = Tempstore::load('theme_loaded');
        try {
            $authentications = new Authentication();
            $authentication_method = $authentications->getAuthenticationMethod();
            if($authentication_method) {

                /**@var $_callback AuthenticationInterface **/
                $_callback = $authentication_method['_callback'];

                /**@var $_success Route **/
                $_success = $authentication_method['_success_route'];

                /**@var $_error Route **/
                $_error = $authentication_method['_error_route'];

                $_callback->authenticate($this->request);

                $_callback->success($_success);

                $_callback->error($_error);

                $this->response->setContentType(ContentType::TEXT_HTML)
                    ->setStatusCode(StatusCode::OK)
                    ->write($theme->view($_callback->getTheme(), []));
                return;
            }
        }catch (\Throwable $e){
            (new ErrorSystem())->setException($e);
            $this->response->setContentType(ContentType::TEXT_HTML)
                ->setStatusCode(StatusCode::OK)->write($theme->view('mini_no_login_view.php'));
        }
    }
}