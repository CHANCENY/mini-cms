<?php

namespace Mini\Cms\Modules\Authentication;

use Mini\Cms\Modules\Authentication\PasswordNormal\PasswordNormal;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Routing\Route;

class Authentication
{
    private array $authentication_methods;

    /**
     * @var array|string[]
     */
    private array $authentication_method;

    /**
     * @throws AuthenticationMethodNotExistException
     */
    public function __construct()
    {
        $this->authentication_methods = array(
            'basic' => array(
                '_callback' => PasswordNormal::class,
                '_active' => true,
                '_success_route' => new Route('dc551189-d1e1-4d56-b331-2bf50956c957'),
                '_error_route' => new Route('b61763e1-9193-45a4-8a7e-61c7235b50ad'),
            )
        );

        Extensions::runHooks('_authentication_method_alter',[&$this->authentication_methods]);

        if(empty($this->authentication_methods)) {
            throw new AuthenticationMethodNotExistException("Authentication method not exists");
        }
        foreach ($this->authentication_methods as $key=>&$method) {
            $_callback = $method['_callback'];
            $_callback = new $_callback();
            if($_callback instanceof AuthenticationInterface) {
                if(isset($method['_active']) && $method['_active']) {
                    $method['_callback'] = $_callback;
                    $this->authentication_method = $method;
                }
            }else {
                unset($this->authentication_methods[$key]);
            }
        }
    }

    public function getAuthenticationMethods(): array
    {
        return $this->authentication_methods;
    }

    public function getAuthenticationMethod(): array
    {
        return $this->authentication_method;
    }

    /**
     * @throws AuthenticationMethodNotExistException
     */
    public function getAuthenticationMethodByName(string $name): array
    {
        if(isset($this->authentication_methods[$name])) {
            return $this->authentication_methods[$name];
        }
        throw new AuthenticationMethodNotExistException("Authentication method not exists");
    }


}