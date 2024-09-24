<?php

namespace Mini\Cms\Modules\Content\Tait;

use Symfony\Component\Yaml\Yaml;

trait ActionTrait
{
    protected array $FIELD;

    protected array $STORAGE;

    protected array $CONTENT_TYPE;

    protected function write(string $location, mixed $data): bool
    {
        $data = Yaml::dump($data);
        if(file_exists($location)) {
            throw new ConfigFileAlreadyExistException("configuration $location already exist");
        }
        return !empty(file_put_contents($location, $data));
    }

    protected function read(string $location): array|null
    {
        if(!file_exists($location)) {
            return null;
        }
        return Yaml::parseFile($location);
    }

    protected function prepare()
    {
        @mkdir("private://configs/storages");
        @mkdir("private://configs/types");
        @mkdir("private://configs/fields");
        @file_put_contents("private://configs/.htaccess", 'Deny from all');
    }
}