<?php

namespace Mini\Cms\Connections\Database\Queries;

use PDO;
use PDOStatement;

/**
 * @class QueryManager handle queries for database.
 */
class QueryManager
{
    /**
     * @var array
     */
    private array $fields = [];
    /**
     * @var array
     */
    private array $conditions = [];
    /**
     * @var string
     */
    private string $query = '';
    /**
     * @var PDO
     */
    private PDO $connection;
    /**
     * @var array|string[]
     */
    private array $selectFields = ['*'];
    /**
     * @var string
     */
    private string $table = '';
    /**
     * @var string
     */
    private string $alias = '';
    /**
     * @var PDOStatement|false
     */
    private PDOStatement|false $pdo_statement;
    /**
     * @var bool
     */
    private bool $inTransaction = false;

    /**
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getSelectFields(): array
    {
        return $this->selectFields;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getPdoStatement(): bool|PDOStatement
    {
        return $this->pdo_statement;
    }

    public function isInTransaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->connection->beginTransaction();
        }
        return $this->inTransaction;
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        if ($this->inTransaction) {
            $this->inTransaction = !$this->connection->commit();
        }
        return !$this->inTransaction;
    }

    /**
     * @return bool
     */
    public function rollback(): bool
    {
        if ($this->inTransaction) {
            $this->inTransaction = !$this->connection->rollBack();
        }
        return !$this->inTransaction;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public function select(string $table, string $alias = ''): QueryManager
    {
        $this->table = $table;
        $this->alias = $alias;
        $this->buildSelectQuery();
        return $this;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function insert(string $table): QueryManager
    {
        $this->query = "INSERT INTO $table";
        return $this;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function update(string $table): QueryManager
    {
        $this->query = "UPDATE $table";
        return $this;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function delete(string $table): QueryManager
    {
        $this->query = "DELETE FROM $table";
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function addField(string $field, mixed $value): QueryManager
    {
        $this->fields[$field] = $value;
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @param string $conjunction
     * @return $this
     * @throws \Exception
     */
    public function addCondition(string $field, mixed $value, string $operator = '=', string $conjunction = 'AND'): QueryManager
    {
        $this->conditions[] = compact('field', 'value', 'operator', 'conjunction');
        $this->buildConditions();
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function addConditions(array $conditions): QueryManager
    {
        $this->conditions = array_merge($this->conditions, $conditions);
        $this->buildConditions();
        return $this;
    }

    /**
     * @param string $table
     * @param string $alias
     * @param string $on
     * @return $this
     */
    public function leftJoin(string $table, string $alias, string $on): QueryManager
    {
        $this->query .= " LEFT JOIN $table AS $alias ON $on";
        return $this;
    }

    /**
     * @param string $table
     * @param string $alias
     * @param string $on
     * @return $this
     */
    public function rightJoin(string $table, string $alias, string $on): QueryManager
    {
        $this->query .= " RIGHT JOIN $table AS $alias ON $on";
        return $this;
    }

    /**
     * @param string $table
     * @param string $alias
     * @param string $on
     * @return $this
     */
    public function innerJoin(string $table, string $alias, string $on): QueryManager
    {
        $this->query .= " INNER JOIN $table AS $alias ON $on";
        return $this;
    }

    /**
     * @param string $table
     * @param string $alias
     * @param string $on
     * @return $this
     */
    public function outerJoin(string $table, string $alias, string $on): QueryManager
    {
        $this->query .= " OUTER JOIN $table AS $alias ON $on";
        return $this;
    }

    /**
     * @param int $start
     * @param int $end
     * @return $this
     */
    public function range(int $start, int $end): QueryManager
    {
        $this->query .= " LIMIT $start, $end";
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function groupBy(string $field): QueryManager
    {
        $this->query .= " GROUP BY $field";
        return $this;
    }

    /**
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function orderBy(string $field, string $direction = 'DESC'): QueryManager
    {
        $this->query .= " ORDER BY $field $direction";
        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): QueryManager
    {
        $this->query .= " LIMIT $limit";
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): QueryManager
    {
        $this->query .= " OFFSET $offset";
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function selectFields(array $fields): QueryManager
    {
        $this->selectFields = $fields;
        $this->buildSelectQuery(); // Rebuild the select query with new fields
        return $this;
    }

    /**
     * @return void
     */
    private function buildSelectQuery(): void
    {
        $fields = implode(', ', $this->selectFields);
        $this->query = "SELECT $fields FROM $this->table" . ($this->alias ? " AS $this->alias" : '');
    }

    /**
     * @return void
     */
    private function buildInsertQuery(): void
    {
        $columns = implode(', ', array_keys($this->fields));
        $placeholders = implode(', ', array_fill(0, count($this->fields), '?'));
        $this->query .= " ($columns) VALUES ($placeholders)";
    }

    /**
     * @return void
     */
    private function buildUpdateQuery(): void
    {
        $setClause = implode(', ', array_map(fn($field) => "$field = ?", array_keys($this->fields)));
        $this->query .= " SET $setClause";
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function buildConditions(): void
    {
        if (!empty($this->conditions)) {
            $queryConditions = [];
            $bindings = [];

            foreach ($this->conditions as $condition) {
                $field = $condition['field'];
                $operator = strtoupper($condition['operator']);
                $value = $condition['value'];
                $conjunction = $condition['conjunction'] ?? null;

                switch ($operator) {
                    case '=':
                    case '!=':
                    case '>':
                    case '<':
                    case '>=':
                    case '<=':
                        $queryConditions[] = "`$field` $operator ?";
                        $bindings[] = $value;
                        break;
                    case 'IN':
                    case 'NOT IN':
                        $placeholders = implode(', ', array_fill(0, count((array)$value), '?'));
                        $queryConditions[] = "`$field` $operator ($placeholders)";
                        $bindings = array_merge($bindings, (array)$value);
                        break;
                    default:
                        throw new \Exception("Unsupported operator: $operator");
                }

                if ($conjunction && $condition !== end($this->conditions)) {
                    $queryConditions[] = strtoupper($conjunction);
                }
            }

            $this->query .= " WHERE " . implode(' ', $queryConditions);
            $this->bindings = $bindings; // Store bindings for use in execute method
        }
    }

    /**
     * @return PDOStatement
     * @throws \Exception
     */
    public function execute(): PDOStatement
    {
        switch (true) {
            case str_starts_with($this->query, 'INSERT'):
                $this->buildInsertQuery();
                break;
            case str_starts_with($this->query, 'UPDATE'):
                $this->buildUpdateQuery();
                break;
            case str_starts_with($this->query, 'SELECT'):
            case str_starts_with($this->query, 'DELETE'):
                // No additional query building required for SELECT and DELETE
                break;
            default:
                throw new \Exception("Unsupported query type.");
        }
        $stmt = $this->connection->prepare($this->query);

        $bindings = [];
        foreach ($this->fields as $value) {
            $bindings[] = $value;
        }
        foreach ($this->conditions as $condition) {
            if (is_array($condition['value'])) {
                foreach ($condition['value'] as $value) {
                    $bindings[] = $value;
                }
            } else {
                $bindings[] = $condition['value'];
            }
        }

        $stmt->execute($bindings);
        $this->pdo_statement = $stmt;
        return $stmt;
    }

    /**
     * @return false|string
     */
    public function lastInsertId(): false|string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * @return int
     */
    public function rowCount(): int
    {
        return $this->pdo_statement->rowCount();
    }

    /**
     * @return mixed
     */
    public function fetch()
    {
        return $this->pdo_statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return false|array
     */
    public function fetchAll(): false|array
    {
        return $this->pdo_statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return mixed
     */
    public function fetchColumn(): mixed
    {
        return $this->pdo_statement->fetchColumn();
    }

    /**
     * @return mixed
     */
    public function fetchObject(): mixed
    {
        return $this->pdo_statement->fetchObject();
    }

    /**
     * @return mixed
     */
    public function fetchAllObject(): mixed
    {
        return $this->pdo_statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @return mixed
     */
    public function fetchRow(): mixed
    {
        return $this->pdo_statement->fetch(PDO::FETCH_NUM);
    }

    /**
     * @return mixed
     */
    public function fetchAllRow(): mixed
    {
        return $this->pdo_statement->fetchAll(PDO::FETCH_NUM);
    }
}