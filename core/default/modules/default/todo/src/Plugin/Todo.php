<?php

namespace Mini\Cms\default\modules\default\todo\src\Plugin;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Mini;

class Todo
{
    public static function create(string $task, int $timestamp): int|bool
    {
        $query = "INSERT INTO todo_tasks (task, time_stamp) VALUES (:task, :t)";
        $query = Mini::connection()->prepare($query);
        $query->bindValue(':task', $task);
        $query->bindValue(':t', $timestamp);
        $query->execute();
        return Mini::connection()->lastInsertId();
    }

    public static function getTasks(): array
    {
        $query = Mini::connection()->prepare("SELECT * FROM todo_tasks ORDER BY tid DESC ");
        $query->execute();
        return $query->fetchAll();
    }

    public static function delete(int $task_id): bool
    {
        $query = Mini::connection()->prepare("DELETE FROM todo_tasks WHERE tid = :t");
        $query->bindValue(':t', $task_id);
        return $query->execute();
    }

    public static function bulkDelete(array $task_ids): bool
    {
        $query = Mini::connection()->prepare("DELETE FROM todo_tasks WHERE tid IN (". implode(',', array_values($task_ids)). ")");
        return $query->execute();
    }

    public static function get(int $task_id): ?array
    {
        $query = Mini::connection()->prepare("SELECT * FROM todo_tasks WHERE tid = :t");
        $query->bindValue(':t', $task_id);
        $query->execute();
        return $query->fetch();
    }
}