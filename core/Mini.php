<?php

namespace Mini\Cms;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Connections\Database\Queries\QueryManager;
use Mini\Cms\Entities\Node;
use Mini\Cms\Entities\Term;
use Mini\Cms\Entities\User;
use Mini\Cms\Modules\CurrentUser\Authenticator;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\FileSystem\FileSystem;
use Mini\Cms\Modules\Messenger\Messenger;
use Mini\Cms\Modules\Site\Site;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\System\System;
use Mini\Cms\Theme\MarkUp;
use Mini\Cms\Theme\Theme;

/**
 *
 */
class Mini
{
    /**
     * @return CurrentUser
     */
    public static function currentUser(): CurrentUser
    {
        return new CurrentUser();
    }

    /**
     * @return Database
     */
    public static function database(): Database
    {
        return new Database();
    }

    /**
     * @return ConfigFactory
     */
    public static function config(): ConfigFactory
    {
        return new ConfigFactory();
    }

    /**
     * @return \PDO|null
     */
    public static function connection(): ?\PDO
    {
        return Database::database();
    }

    /**
     * @return Site
     */
    public static function site(): Site
    {
        return new Site();
    }

    /**
     * @param array $theme
     * @return Theme
     */
    public static function theme(array $theme): Theme
    {
       return new Theme($theme);
    }

    /**
     * @return Authenticator
     */
    public static function Authentication(): Authenticator
    {
        return new Authenticator();
    }

    /**
     * @return Node
     */
    public static function node(): Node
    {
        return new Node();
    }

    /**
     * @return Term
     */
    public static function term(): Term
    {
        return new Term();
    }

    /**
     * @param int $uid
     * @return User
     */
    public static function user(int $uid): User
    {
        return new User($uid);
    }

    /**
     * @throws \Exception
     */
    public static function MarkUp(string $theme_name): MarkUp
    {
        return new MarkUp($theme_name);
    }

    /**
     * @return System
     */
    public static function System(): System
    {
        return new System();
    }

    /**
     * @param int|string $module_name
     * @return ModuleHandler
     */
    public static function module(int|string $module_name): ModuleHandler
    {
        return new ModuleHandler($module_name);
    }

    /**
     * @return FileSystem
     */
    public static function fileSystem(): FileSystem
    {
        return new FileSystem();
    }


    /**
     * @return File
     */
    public static function file(): File
    {
        return new File();
    }

    /**
     * @return RouteBuilder
     */
    public static function routeBuilder(): RouteBuilder
    {
        return new RouteBuilder();
    }

    /**
     * @return QueryManager
     */
    public static function queryManager(): QueryManager
    {
        return new QueryManager(Database::database());
    }

    public static function messenger(): Messenger
    {
        return new Messenger();
    }
}