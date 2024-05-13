<?php

namespace Mini\Cms\Entities;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Services\Services;
use PDO;

class User
{
    private int $uid;

    private string $name;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function setCreated(string $created): void
    {
        $this->created = $created;
    }

    public function setUpdated(string $updated): void
    {
        $this->updated = $updated;
    }

    public function setActive(string $active): void
    {
        $this->active = $active;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getCreated(): string
    {
        return $this->created;
    }

    public function getUpdated(): string
    {
        return $this->updated;
    }

    public function getActive(): string
    {
        return $this->active;
    }

    public function getUserTablesQuery(): string
    {
        return $this->user_tables_query;
    }

    public function getUser(): mixed
    {
        return $this->user;
    }

    private string $email;

    private string $password;

    private string $role;

    private string $firstname;

    private string $lastname;
    
    private string $created;
    
    private string $updated;
    
    private string $active;

    private string $image;
    
    private string $user_tables_query;
    /**
     * @var array|mixed
     */
    private mixed $user;

    public function __construct(int $uid)
    {
        $this->uid = $uid;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->firstname = '';
        $this->lastname = '';
        $this->created = '';
        $this->updated = '';
        $this->active = '';
        $this->image = '';

        $this->user_tables_query = "users";
        $this->userTable();
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $userConfig = $config->get('user_extended_tables');
            if(!empty($userConfig)) {
                foreach($userConfig as $userConfigValue) {
                    $this->user_tables_query .= " LEFT JOIN $userConfigValue ON $userConfigValue.uid = users.uid";
                }
            }
        }

        $this->user_tables_query = "SELECT * FROM $this->user_tables_query WHERE users.uid = :uid";
        $query = Database::database()->prepare($this->user_tables_query);
        $query->bindParam(':uid', $this->uid);
        $query->execute();
        
        $data = $query->fetch(PDO::FETCH_ASSOC) ?? [];
        if(!empty($data)) {
            $this->uid = $data['uid'];
            $this->name = $data['name'];
            $this->email = $data['email'];
            $this->password = $data['password'];
            $this->role = $data['role'];
            $this->firstname = $data['firstname'];
            $this->lastname = $data['lastname'];
            $this->created = $data['created'];
            $this->updated = $data['updated'];
            $this->active = $data['active'];
            $this->image = $data['image'];
            $this->user = $data;

        }
    }

    private function userTable(): void
    {
        $database = new Database();
        $query = null;
        if($database->getDatabaseType() === 'sqlite') {
            $query = "CREATE TABLE IF NOT EXISTS $this->user_tables_query (uid INTEGER PRIMARY KEY AUTOINCREMENT,email varchar(255), name varchar(255), password varchar(255), role varchar(255), active varchar(255), created varchar(255), updated varchar(255), firstname varchar(255), lastname varchar(255), image varchar(30))";
        }
        if($database->getDatabaseType() === 'mysql') {
            $query = "CREATE TABLE IF NOT EXISTS $this->user_tables_query (uid int(11) PRIMARY KEY AUTO_INCREMENT,email varchar(255), name varchar(255), password varchar(255), role varchar(255), active varchar(255), created varchar(255), updated varchar(255), firstname varchar(255), lastname varchar(255), image varchar(30))";

        }
        $query = Database::database()->prepare($query);
        $query->execute();
    }

    public function getValues()
    {
        return $this->user;
    }

    public function get(string $key)
    {
        return $this->user[$key] ?? null;
    }

    public function user(array $data): static
    {
        $this->name = $data['name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->firstname = $data['firstname'] ?? null;
        $this->lastname = $data['lastname'] ?? null;
        $this->created = time();
        $this->updated = time();
        $this->active = !empty($data['active']) ? 1 : 0;
        $this->image = is_numeric($data['image']) ? $data['image']  : throw new \Exception("Image should be fid");
        return $this;
    }

    public function save():int
    {
        if($this->userExist($this->email, $this->name) === true) {

            $con = Database::database();
            $query = $con->prepare(
                "INSERT INTO users (name, email, password, role, active, created, updated, firstname, lastname,image) VALUES (:name, :email, :password, :role, :active, :created, :updated, :firstname, :lastname, :image)"
            );
            $query->bindParam(':name', $this->name);
            $query->bindParam(':email', $this->email);
            $query->bindParam(':password', $this->password);
            $query->bindParam(':role', $this->role);
            $query->bindParam(':active', $this->active);
            $query->bindParam(':created', $this->created);
            $query->bindParam(':updated', $this->updated);
            $query->bindParam(':firstname', $this->firstname);
            $query->bindParam(':lastname', $this->lastname);
            $query->bindParam(':image', $this->image);

            $query->execute();
            $this->uid = $con->lastInsertId();
            return $this->uid;
        }
        return $this->uid;
    }

    public static function create(array $user_fields): int
    {
        return (new static(0))->user($user_fields)->save();
    }

    private function userExist(mixed $email, mixed $name): bool
    {
        $con = Database::database();
        $query = $con->prepare("SELECT * FROM users WHERE email = :email");
        $query->bindParam(':email', $email);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if(!empty($data)) {
            $this->uid = $data['uid'];
            return false;
        }
        $query = $con->prepare("SELECT * FROM users WHERE name = :name");
        $query->bindParam(':name', $name);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if(!empty($data)) {
            $this->uid = $data['uid'];
            return false;
        }
        return true;
    }

    public static function load(int $uid): static
    {
        return new static($uid);
    }
}