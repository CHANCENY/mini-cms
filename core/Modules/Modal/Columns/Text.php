<?php

namespace Mini\Cms\Modules\Modal\Columns;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Modal\Modal;

class Text implements ColumnInterface
{
    protected array $column_settings = [];

    protected Modal $parent;

    public function __construct()
    {
        $this->column_settings = [
            "type" => "text",
        ];
    }

    public function name(string $name): static
    {
        $this->column_settings['name'] = $name;
        return $this;
    }

    public function description(string $description): static
    {
        $this->column_settings['description'] = $description;
        return $this;
    }

    public function getType(): string
    {
        return $this->column_settings['type'];
    }

    public function size(int $size): static
    {
        $this->column_settings['size'] = $size;
        return $this;
    }

    public function primary(bool $primary): static
    {
        $this->column_settings['primary'] = $primary ? "PRIMARY KEY " : "";
        return $this;
    }

    public function unique(bool $unique): static
    {
        $this->column_settings['unique'] = $unique ? "UNIQUE KEY " : "";
        return $this;
    }

    public function nullable(bool $nullable): static
    {
        $this->column_settings['nullable'] = $nullable ? "NULL " : "NOT NULL ";
        return $this;
    }

    public function autoIncrement(bool $autoIncrement): static
    {
        $this->column_settings['auto_increment'] = $autoIncrement ? "AUTO_INCREMENT " : "";
        return $this;
    }

    public function setAsDefined(mixed $value): static
    {
        $this->column_settings['as_defined'] = $value ? "DEFAULT '$value' " : "";
        return $this;
    }

    public function create(): bool
    {
        if(!empty($this->column_settings['type']) && !empty($this->column_settings['name'])) {
            $query_line = "ALTER TABLE {$this->parent->getMainTable()} ADD COLUMN {$this->column_settings['name']} TEXT ";

            if(!empty($this->column_settings['nullable'])) {
                $query_line .= " ". $this->column_settings['nullable'];
            }

            if(!empty($this->column_settings['description'])) {
                $query_line .= " COMMENT '". $this->column_settings['description']."'";
            }

            if(!empty($this->column_settings['as_defined'])) {
                $query_line .= " ". $this->column_settings['as_defined'];
            }
            try{
                return Database::database()->prepare($query_line)->execute();
            }catch (\PDOException $e){
                return false;
            }

        }
        return false;
    }

    public function parent(Modal &$parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    public function getName(): string
    {
        return $this->column_settings['name'];
    }

    public function getDescription(): string
    {
        return $this->column_settings['description'];
    }

    public function getSize(): int
    {
        return $this->column_settings['size'];
    }

    public function isNullable(): bool
    {
        return !empty($this->column_settings['nullable']) && trim($this->column_settings['nullable']) === "NULL";
    }

    public function isAutoIncrement(): bool
    {
        return !empty($this->column_settings['auto_increment']);
    }

    public function isPrimary(): bool
    {
        return !empty($this->column_settings['primary']);
    }

    public function isUnique(): bool
    {
        return !empty($this->column_settings['unique']);
    }

    public function isDefined(): bool
    {
        return !empty($this->column_settings['as_defined']);
    }
}