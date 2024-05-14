<?php


namespace Mini\Cms\Modules\Storage;

class Tempstore
{
    private array $session;

    public function __construct()
    {
        $this->session = $_SESSION['temp_store'] ?? [];
//        foreach ($this->session as $key=>$value){
//            $expire_date = $value['expire_time'] ?? 0;
//
//            if($expire_date < time()) {
//                unset($this->session[$key]);
//                unset($_SESSION['temp_store'][$key]);
//            }
//        }
    }

    public function set(string $key, mixed $data, int $time = 0): bool
    {
        $storable = [
            'data' => $data,
            'expire_time' => !empty($time)? $time : time() * 60
        ];
        $_SESSION['temp_store'][$key] = $storable;
        $this->session[$key] = $storable;
        return !empty($_SESSION['temp_store'][$key]);
    }

    public function get(string $key): mixed
    {
        return $this->session[$key]['data'] ?? null;
    }

    public function clear(string $key): bool
    {
        if(isset($this->session[$key])) {
            unset($this->session[$key]);
            unset($_SESSION['temp_store'][$key]);
        }
        return empty($this->session[$key]);
    }

    public static function save(string $key, mixed $data, int $how_long = 0): bool
    {
        return (new Tempstore())->set($key,$data, empty($how_long) ? time() * 60 * 24 : $how_long);
    }

    public static function load(string $key): mixed
    {
        return (new Tempstore())->get($key);
    }

    public static function delete(string $key): bool
    {
        return (new Tempstore())->clear($key);
    }
}