<?php

namespace Mini\Cms\Modules\Access;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Services\Services;

class Roles
{
    private array $roles = [];

    public function __construct()
    {
        $this->roles = [
            [
                'name' => 'administrator',
                'label' => 'Administrator',
                'permissions' => [
                    "administrator_access",
                    "authenticated_access"
                ]

            ],
            [
                'name' => 'authenticated',
                'label' => 'Authenticated',
                'permissions' => [
                    "authenticated_access",
                ]
            ],
            [
                'name' => 'anonymous',
                'label' => 'Anonymous',
                'permissions' => [
                    "anonymous_access",
                ]
            ]
        ];
        $roles_in_config = Services::create('config.factory')?->get('access')['roles'] ?? [];
        $this->roles = array_merge($this->roles, $roles_in_config);
        $this->roles = $this->uniqueByName($this->roles);
        Extensions::runHooks('_user_roles_list_alter',[&$this->roles]);
    }

    public function getRoles(): array
    {
        return array_map(function ($item){
            return new Role($item['name']);
        },$this->roles);
    }

    public function getRole(string $name): Role|null
    {
        $found = array_filter($this->roles,function ($item) use ($name){
           return $item['name'] === $name;
        });
        $found = reset($found);
        return $found ? new Role($found['name']) : null;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles[] = $roles;
    }

    public function saveRole(): bool
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $access = $config->get('access');
            $access['roles'] = $this->roles;
            $config->set('access', $access);
            return $config->save();
        }
        return false;
    }

    private function uniqueByName($array): array
    {
        $new_array = [];
        foreach ($array as $key=>$name) {
            if(!empty($new_array)) {
                $l = $name['name'];
                $con = array_filter($new_array, function ($item) use ($l) {
                    return $item['name'] === $l;
                });
                if(empty($con)) {
                    $new_array[] = $name;
                }
            }
            else{
                $new_array[] = $name;
            }

        }
        return $new_array;
    }

    public function getPermissions(string $role): array
    {
        return $this->getRole($role)['permissions'] ?? ['anonymous_access'];
    }

}