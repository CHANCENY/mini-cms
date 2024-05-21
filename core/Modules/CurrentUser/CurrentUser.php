<?php

namespace Mini\Cms\Modules\CurrentUser;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\StorageManager\Connector;

class CurrentUser
{
    private mixed $current_user;

    public function __construct()
    {
        $this->current_user = Tempstore::load('default_current_user') ?? [];
    }

    public function setCurrentUser(mixed $current_user): static
    {
        $this->current_user = $current_user;
        return $this;
    }

    public static function load(mixed $current_user): CurrentUser
    {
        return (new CurrentUser())->setCurrentUser($current_user);
    }
    public function getAccountName(): string
    {
        return $this->current_user['name'] ?? '';
    }

    public function getAccountEmail(): string
    {
        return $this->current_user['email'] ?? '';
    }

    public function getRoles(): array
    {
        $roles = explode(',',$this->current_user['role'] ?? '');
        if(empty(reset($roles))) {
            return ['anonymous'];
        }
        return $roles;
    }

    public function getFirstName(): string
    {
        return $this->current_user['firstname'] ?? '';
    }

    public function getLastName(): string
    {
        return $this->current_user['lastname'] ?? '';
    }

    public function isActive():bool
    {
        return !empty($this->current_user['active'] ?? null);
    }

    public function isAdmin():bool
    {
        return in_array('administrator', $this->getRoles());
    }

    public function isAuthenticated(): bool
    {
        return in_array('authenticated', $this->getRoles()) || in_array('administrator', $this->getRoles());
    }

    public function id()
    {
        return $this->current_user['uid'] ?? 0;
    }

    public function getImage(): ?string
    {
       $mid = $this->current_user['image'] ?? 0;
       if (empty($mid)) {
           return null;
       }

       $file = File::load((int) $mid);
       return $file->resolveStyleUri();
    }
}