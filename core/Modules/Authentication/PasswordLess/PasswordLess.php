<?php

namespace Mini\Cms\Modules\Authentication\PasswordLess;

use Mini\Cms\Connections\Database\Queries\QueryManager;
use Mini\Cms\Connections\Smtp\MailManager;
use Mini\Cms\Connections\Smtp\Receiver;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Authentication\AuthenticationInterface;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PasswordLess implements AuthenticationInterface
{

    private bool|null $is_authenticated = null;

    public function getIsAuthenticated(): ?bool
    {
        return $this->is_authenticated;
    }

    public function setIsAuthenticated(?bool $is_authenticated): void
    {
        $this->is_authenticated = $is_authenticated;
    }

    public function getTheme(): string
    {
        return 'login_password_less.php';
    }

    /**
     * @throws \Exception
     */
    public function authenticate(Request $request): void
    {
        if($request->isMethod(Request::METHOD_POST)) {
            $email = $request->request->get('username');
            $queryManager = new QueryManager(Mini::connection());
            $queryManager->select('users');
            $queryManager->selectFields(['email','active', 'uid', 'firstname']);
            if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $queryManager->addCondition('email', htmlspecialchars(strip_tags($email)));
            }
            else {
                $queryManager->addCondition('name', htmlspecialchars(strip_tags($email)));
            }
            $statement = $queryManager->execute();
            $user = $statement->fetch();

            if(isset($user['active']) && $user['active'] === '1') {
                $code = time();
                sleep(2);
                $session_code = time();
                Tempstore::save('password_less_'.$code,$user);
                Tempstore::save('password_less_request',$request);
                $options = [
                    'session_code' => $session_code,
                    'auth_method' => 'password_less',
                    'code' => $code,
                ];
                $new_redirect_route = new Route('905d8ebb-1086665f-6669674d3a-85747801a-6g0598584-6958473-690493838-i765432345678');
                $url = $request->getSchemeAndHttpHost() . '/'. trim($new_redirect_route->replacePlaceholdersInUrl($options), '/');
                $params = [
                    'subject' => 'Password Less Login Confirmation',
                    'body' => "<p>Hello {$user['firstname']},<br>You have requested for password less login. Please continue the process by clicking with link below
                                  <br><br> <a href='{$url}'>Continue</a><br><br>Ignore this email if you didn`t made this request.</p>"
                ];
                $receiver = new Receiver([
                    ['name' => $user['firstname'], 'mail' => $user['email'] ]
                ]);
                MailManager::mail($receiver)->send($params);
                Mini::messenger()->addMessage("We have sent an email to continue with this process please read instruction in email sent");
            }
        }
    }

    public function success(Route $success_route): void
    {
        if($this->is_authenticated) {
            (new RedirectResponse($success_route->getUrl()))->send();
            exit;
        }
    }

    public function error(Route $error_route): void
    {
        if($this->is_authenticated === false) {
            (new RedirectResponse($error_route->getUrl()))->send();
            exit;
        }
    }

}