<?php

namespace Mini\Cms\Modules\Modal\Columns;

use Mini\Cms\Modules\Modal\Modal;

interface ColumnInterface
{
    /**
     * Give column the name its need.
     * @param string $name name of column to add in table.
     * @return $this
     */
    public function name(string $name): static;

    /**
     * Describe the column usage.
     * @param string $description column description.
     * @return $this
     */
    public function description(string $description): static;

    /**
     * Get a column type
     * @return string
     */
    public function getType(): string;

    /**
     * Required for varchar columns.
     * @param int $size size of column
     * @return $this
     */
    public function size(int $size): static;

    /**
     * Make column primary column
     * @param bool $primary
     * @return $this
     */
    public function primary(bool $primary): static;

    /**
     * Make column to enforce unique value.
     * @param bool $unique
     * @return $this
     */
    public function unique(bool $unique): static;

    /**
     * Make column to accept null.
     * @param bool $nullable
     * @return $this
     */
    public function nullable(bool $nullable): static;

    /**
     * Make column autoincrement.
     * @param bool $autoIncrement
     * @return $this
     */
    public function autoIncrement(bool $autoIncrement): static;

    /**
     * Define value to set as default value.
     * @param mixed $value
     * @return $this
     */
    public function setAsDefined(mixed $value): static;

    /**
     * Get line of column to use for column create for table.
     * @return bool
     */
    public function create(): bool;


    /**
     * Give table name the column belongs to.
     * @param Modal $parent
     * @return $this
     */
    public function parent(Modal &$parent): static;

    /**
     * Get column name.
     * @return string
     */
    public function getName(): string;

    /**
     * Get column description.
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get size of column.
     * @return int
     */
    public function getSize(): int;

    /**
     * Checking if column can be null
     * @return bool
     */
    public function isNullable(): bool;

    /**
     * Checking if column is auto increment.
     * @return bool
     */
    public function isAutoIncrement(): bool;

    /**
     * checking if column is primary.
     * @return bool
     */
    public function isPrimary(): bool;

    /**
     * Checking if column is unique.
     * @return bool
     */
    public function isUnique(): bool;

    /**
     * Checking if column has default value.
     * @return bool
     */
    public function isDefined(): bool;

}