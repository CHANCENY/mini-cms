<?php

namespace Mini\Cms\Modules\Meterical;

use DateTime;
use Mini\Cms\Mini;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\ErrorSystem;

class Meterical 
{

    public static function storage(): void
    {
        try{
            $database = new Database(true);

            if ($database->getDatabaseType() === 'mysql') {
                $query = "CREATE TABLE IF NOT EXIST meterical_records (mid int(11) AUTO_INCREMENT PRIMARY KEY, start_time int(11) NOT NULL, end_time int(11) NOT NULL, uid int(11) NULL, time_taken int(11) NOT NULL, method varchar(22) NOT NULL, uri varchar(400) NULL, ip varchar(255) NULL)";
            }
            if ($database->getDatabaseType() === 'sqlite') {
                $query = "CREATE TABLE IF NOT EXISTS meterical_records (mid INTEGER PRIMARY KEY AUTOINCREMENT, start_time INTEGER NOT NULL, end_time INTEGER NOT NULL, uid INTEGER, time_taken INTEGER NOT NULL, method varchar(22) NOT NULL, uri varchar(400) NULL, ip varchar(255) NULL)";
            }
            $database->connect()->exec($query);
        }catch(\Throwable $e){
            (new ErrorSystem)->setException($e)->save();
        };
    }

    public static function store(array $data): void
    {
        try{
            $values = array_values($data);

            self::storage();
            $query = "INSERT INTO meterical_records (start_time, end_time, uid, time_taken, method, uri, ip) VALUES( :start_time, :end_time , :uid , :time_taken, :method, :uri, :ip)";
            $query = Mini::connection()->prepare($query);
            $query->execute($data);
        }catch(\Throwable $e){
            (new ErrorSystem())->setException($e)->save();
        }
    } 

    public static function getMeterics(): array
    {
        try{
            $query = "SELECT * FROM meterical_records";
            $query = Mini::connection()->prepare($query);
            $query->execute();
            $data = $query->fetchAll();
            $grouped = [
                'method'=> [
                    'POST' => [],
                    'GET' => [],
                    'PATCH' => [],
                    'PUT' => [],
                    'DELETE' => [],
                ],
                'DATE' => [],
                'TIME' => []
            ];

            foreach($data as $value) {
                $grouped['method'][$value['method']]['time_taken'][] = $value['time_taken'];
                $grouped['method'][$value['method']]['ip'][] = $value['ip'];
                $grouped['method'][$value['method']]['uri'][] = $value['uri'];
                $grouped['method'][$value['method']]['end_time'][] = $value['end_time'];
                $grouped['method'][$value['method']]['start_time'][] = $value['start_time'];
                $grouped['DATE'][] = (new DateTime("@".$value['start_time']))->format('d-m-Y');
                $grouped['TIME'][] = (new DateTime("@" . $value['start_time']))->format('h:i:s');
            }
            return $grouped;
        }catch(\Throwable){}
    }
}