<?php

namespace Mini\Cms\Modules\Access;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Routing\Route;

class AccessMiddleWare implements AccessMiddleWareInterface
{
    private bool $passed;

    public function access(Roles $roles, CurrentUser $currentUser, Route $route): AccessMiddleWareInterface
    {
        $this->passed = false;

        $route_permission = $route->getPermission();
        $route_roles = $route->getRoles();

        $current_user_roles = $currentUser->getRoles();

        if(empty($current_user_roles)) {
            $current_user_roles = ['anonymous'];
        }

        // Lets compare roles in route and of current user.
        if(!array_intersect($route_roles,$current_user_roles)) {
            $this->passed = false;
        }

        $permission = [];
        foreach ($route_permission as $key=>$item) {
            if(in_array($key, $current_user_roles)) {
                $permission[] = $item;
            }
        }

        if(!empty($permission)) {
          $this->passed = true;
        }


        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->passed;
    }

    public function onFailedResponse(Response &$response): Response
    {
        $response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::FORBIDDEN)
            ->write("<div class='container mt-lg-5'><div class='m-auto bg-light p-5 rounded bordered'><h4 class='text-center fs-1 text-danger'>Access Denied</h4></div></div>");
        return $response;
    }
}