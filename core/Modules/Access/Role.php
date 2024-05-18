<?php

namespace Mini\Cms\Modules\Access;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Services\Services;

class Role
{
    /**
     * @var false|mixed
     */
    private mixed $role;

    public function __construct(string $role)
    {
        $roles = [
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
        $roles = array_merge($roles, $roles_in_config);
        $roles = $this->uniqueByName($roles);

        $this->role = array_filter($roles, function($item) use ($role) {
            return $item['name'] === $role;
        });
        $this->role = reset($this->role);
    }

    public function getName(): string
    {
        return $this->role['name'] ?? '';
    }

    public function getLabel(): string
    {
        return $this->role['label'] ?? '';
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

    public function getPermissions(): array
    {
        return $this->role['permissions'] ?? ['anonymous_access'];
    }
}