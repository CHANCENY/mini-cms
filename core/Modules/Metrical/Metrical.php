<?php

namespace Mini\Cms\Modules\Metrical;

use DateTime;
use Mini\Cms\Mini;
use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\ErrorSystem;

class Metrical
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
                $grouped['TIME'][] = (new DateTime("@" . $value['start_time']))->format('h:i');
            }
            return $grouped;
        }catch(\Throwable){}
        return [];
    }

    public static function getPerClientMetrics(): array
    {
        try{
            $query = "SELECT ip, method FROM meterical_records GROUP BY ip";
            $query = Mini::connection()->prepare($query);
            $query->execute();
            $data = $query->fetchAll();
            $grouped = [];
            foreach($data as $value) {
                $query = "SELECT * FROM meterical_records WHERE ip = :ip";
                $query = Mini::connection()->prepare($query);
                $query->execute(['ip' => $value['ip']]);
                $data_ip = $query->fetchAll();
                foreach($data_ip as $value_ip) {
                    $grouped['method'][$value['method']]['time_taken'][] = $value_ip['time_taken'];
                    $grouped['method'][$value['method']]['ip'][] = $value_ip['ip'];
                    $grouped['method'][$value['method']]['uri'][] = $value_ip['uri'];
                    $grouped['method'][$value['method']]['end_time'][] = $value_ip['end_time'];
                    $grouped['method'][$value['method']]['start_time'][] = $value_ip['start_time'];
                    $grouped['method']['IP'][] = $value_ip['ip'];
                    $grouped['method']['IP'] = array_unique($grouped['method']['IP']);
                    $grouped['DATE'][] = (new DateTime("@".$value_ip['start_time']))->format('d-m-Y');
                    $grouped['TIME'][] = (new DateTime("@" . $value_ip['start_time']))->format('h:i');
                }
            }
            return $grouped;
        }catch(\Throwable $e){
            (new ErrorSystem())->setException($e)->save();
        };
        return [];
    }

    public static function getTopFiveAccessPages(): array
    {
        try{
            $query = "SELECT *, COUNT(mid) AS total FROM meterical_records GROUP BY uri ORDER BY total DESC LIMIT 5";
            $query = Mini::connection()->prepare($query);
            $query->execute();
            return $query->fetchAll();
        }catch(\Throwable $e){
            (new ErrorSystem())->setException($e)->save();
        }
        return [];
    }

    public static function getAccessData(): array
    {
        try {
            $query = "SELECT mid FROM meterical_records";
            $query = Mini::connection()->prepare($query);
            $query->execute();
            $data = $query->fetchAll();
            $grouped = [];
            $grouped['total_visits'] = count($data);

            $today = new DateTime('today');

            // Clone the $today object to avoid modifying the original instance.
            $today_entry = (clone $today)->setTime(0, 0, 0)->getTimestamp();
            $today_end = (clone $today)->setTime(23, 59, 59)->getTimestamp();

            // Safe query using placeholders
            $query = "SELECT * FROM meterical_records WHERE end_time >= :today_entry AND end_time <= :today_end";
            $query = Mini::connection()->prepare($query);
            $query->execute([
                'today_entry' => $today_entry,
                'today_end' => $today_end
            ]);
            $data = $query->fetchAll();
            $grouped['today_visits'] = count($data);

            $query = "SELECT * FROM users";
            $query = Mini::connection()->prepare($query);
            $query->execute();
            $data = $query->fetchAll();
            $grouped['total_users'] = count($data);

            $query = "SELECT * FROM users WHERE created >= :today_entry AND created <= :today_end";
            $query = Mini::connection()->prepare($query);
            $query->execute([
                'today_entry' => $today_entry,
                'today_end' => $today_end
            ]);
            $data = $query->fetchAll();
            $grouped['today_users'] = count($data);
            return $grouped;
        }catch (\Throwable $e){
            (new ErrorSystem())->setException($e)->save();
        }
        return [];
    }
}