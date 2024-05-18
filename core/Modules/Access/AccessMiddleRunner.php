<?php

namespace Mini\Cms\Modules\Access;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Routing\Route;
use Mini\Cms\Services\Services;

class AccessMiddleRunner
{
    private ?array $middlewares;

    /**
     * Construct running access middleware.
     * @param Route $route
     * @param Response $response
     * @throws \Exception
     */
    public function __construct(private Route $route, private Response &$response)
    {
        $this->middlewares = [];
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            // Loading middlewares from configurations
            $this->middlewares = $config->get('security_middlewares');
        }
    }

    /**
     * Running middleware.
     * @return Response|null
     */
    public function runMiddleWares(): Response|null
    {
        if(!empty($this->middlewares)) {

            // Building required params
            $currentUser = new CurrentUser();
            $roles = new Roles();

            $response = null;

            foreach($this->middlewares as $middleware) {

                // Lets validate middleware
                if(class_exists($middleware)) {
                    $middlewareObject = new $middleware();
                    if($middlewareObject instanceof AccessMiddleWareInterface) {

                        // Calling access
                        $middlewareObject->access($roles,$currentUser,$this->route);

                        // bring results
                        $access_pass = $middlewareObject->isSuccess();
                        if($access_pass) {
                            continue;
                        }
                        else {
                            $response = $middlewareObject->onFailedResponse($this->response);
                        }
                    }
                }
            }
            return $response;
        }
        return null;
    }
}