<?php

namespace Mini\Cms\Modules\CurrentUser;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Connections\Smtp\MailManager;
use Mini\Cms\Connections\Smtp\Receiver;
use Mini\Cms\Controller\Request;
use Mini\Cms\Modules\Storage\Tempstore;


class Authenticator
{
    /**
     * Login user to the site.
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function loginUser(string $email, string $password): bool
    {
        $query = Database::database()->prepare("SELECT * FROM `users` WHERE `email` = :email");
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        if($user) {
            if(password_verify($password, $user['password']) && $user['active'] == 1) {
                Tempstore::save('default_current_user', $user, time() * 60 * 60 * 365);
                return true;
            }
        }

        $query = Database::database()->prepare("SELECT * FROM `users` WHERE `name` = :name");
        $query->execute(['name' => $email]);
        $user = $query->fetch();
        if($user) {
            if(password_verify($password, $user['password']) && $user['active'] == 1) {
                Tempstore::save('default_current_user', $user, time() * 60 * 60 * 365);
                return true;
            }
        }
        return false;
    }

    /**
     * Logout user.
     * @return bool
     */
    public function logoutUser(): bool
    {
        return Tempstore::delete('default_current_user');
    }

    /**
     * Generate verification token.
     * @param int $uid
     * @return string|false
     */
    public function verificationToken(int $uid): string|false
    {
        $query = Database::database()->prepare("SELECT * FROM `users` WHERE `uid` = :id");
        $query->execute(['id' => $uid]);
        $user = $query->fetch();
        if($user) {
            $database = new Database();
            $query = null;
            if($database->getDatabaseType() == 'mysql') {
                $query = "CREATE TABLE IF NOT EXISTS `verification_tokens` (vid int(11) primary key auto_increment, token varchar(255), uid int(11), created_at int(11))";
            }
            if($database->getDatabaseType() == 'sqlite') {
                $query = "CREATE TABLE IF NOT EXISTS `verification_tokens` (vid INTEGER primary key autoincrement, token varchar(255), uid int(11) created_at int(11))";
            }
            $query = Database::database()->prepare($query);
            $query->execute();

            $time = time();
            $query = Database::database()->prepare("INSERT INTO `verification_tokens` (`token`, `uid`, `created_at`) VALUES (:token, :uid, :created_at)");
            $query->execute(['token' => $time, 'uid' => $uid, 'created_at' => $time]);

            return '/user/verify/' . $time;
        }
        return false;
    }

    /**
     * Verification of token.
     * @param string $token
     * @return bool
     */
    public function verifyToken(string $token): bool
    {
        $query = Database::database()->prepare("SELECT * FROM `verification_tokens` WHERE `token` = :token");
        $query->execute(['token' => $token]);
        $data = $query->fetch();
        if($data) {
            $created = $data['created_at'];
            $timestampTwo = time(); // Current timestamp
            $uid = $data['uid'];

            // Convert timestamps to DateTime objects
            $dateOne = new \DateTime("@$created");
            $dateTwo = new \DateTime("@$timestampTwo");

            // Add one-hour to Timestamp One
            $dateOne->modify('+1 hour');

            // Check if Timestamp One is one hour in the past compared to Timestamp Two
            if ($dateOne < $dateTwo) {
                return false;
            } else {
                $query = Database::database()->prepare("UPDATE `users` SET `active` = 1 WHERE `uid` = :uid");
                return $query->execute(['uid' => $uid]);
            }

        }
        return false;
    }


    public function passwordResetToken(string $name): bool
    {
        $query = Database::database()->prepare("SELECT * FROM `users` WHERE `name` = :name");
        $query->execute(['name' => $name]);
        $user = $query->fetch();
        if(empty($user)) {
            $query = Database::database()->prepare("SELECT * FROM `users` WHERE `email` = :email");
            $query->execute(['email' => $name]);
            $user = $query->fetch();
            if(empty($user)) {
                return false;
            }
        }

        $request = Request::createFromGlobals();
        $email = $user['email'];
        $uid = $user['uid'];
        $token = time();
        $reset_token_url = $request->getSchemeAndHttpHost(). '/user/reset-password/' . $token;
        $query = Database::database()->prepare("INSERT INTO verification_tokens (token, uid, created_at) VALUES (:token, :uid, :created_at)");
        $query->execute(['token' => $token, 'uid' => $uid, 'created_at' => $token]);

        return MailManager::mail(
            new Receiver([
                [
                    'mail' => $email,
                    'name' => $user['firstname'] . ' ' . $user['lastname'],
                ]
            ])
        )->send([
            'subject' => 'Password reset',
            'body' => '<p>Hi ' . $user['name'] . ',</p>
                       <p>We have received a password reset request for your account.</p>
                       <p>To reset your password, please click the link below.</p>
                       <p><a href="' . $reset_token_url . '">Reset password link</a></p>'
        ]);
    }


    public function changePassword(string $token, string $newPassword): bool
    {
        $query = Database::database()->prepare("SELECT * FROM `verification_tokens` WHERE `token` = :token");
        $query->execute(['token' => $token]);
        $data = $query->fetch();
        if(empty($data)) {
            return false;
        }

        $uid = $data['uid'];
        $query = Database::database()->prepare("UPDATE `users` SET `password` = :password, updated = :up WHERE `uid` = :uid");
        return $query->execute(['password' => password_hash($newPassword,PASSWORD_BCRYPT), 'uid' => $uid, 'up'=>time()]);
    }

}