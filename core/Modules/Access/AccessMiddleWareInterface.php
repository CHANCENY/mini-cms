<?php
/**
 * This interface describes the requirement of middleware in this mini-cms.
 */
namespace Mini\Cms\Modules\Access;

use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Routing\Route;

interface AccessMiddleWareInterface
{
    /**
     * Access method will be called first on a middleware object.
     * @param Roles $roles Roles class object.
     * @param CurrentUser $currentUser Current user object.
     * @param Route $route Current Route object.
     * @return AccessMiddleWareInterface
     */
    public function access(Roles $roles, CurrentUser $currentUser, Route $route): AccessMiddleWareInterface;

    /**
     * Evaluation in access method its finding true or false will be access by this method.
     * True if all check are good to go.
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * The Last method to be called is this one. This function will be called only if isSuccess returns false.
     * @param Response &$response
     * @return Response
     */
    public function onFailedResponse(Response &$response): Response;
}