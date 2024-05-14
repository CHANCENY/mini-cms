<?php

namespace Mini\Cms\Modules\CurrentUser;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Storage\Tempstore;


class Authenticator
{
    public function loginUser(string $email, string $password): bool
    {
        $query = Database::database()->prepare("SELECT * FROM `users` WHERE `email` = :email");
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        if($user) {
            if(password_verify($password, $user['password'])) {
                Tempstore::save('default_current_user', $user, time() * 60 * 60 * 365);
                return true;
            }
        }

        $query = Database::database()->prepare("SELECT * FROM `users` WHERE `name` = :name");
        $query->execute(['name' => $email]);
        $user = $query->fetch();
        if($user) {
            if(password_verify($password, $user['password'])) {
                Tempstore::save('default_current_user', $user, time() * 60 * 60 * 365);
                return true;
            }
        }
        return false;
    }

    public function logoutUser(): bool
    {
        return Tempstore::delete('default_current_user');
    }
}