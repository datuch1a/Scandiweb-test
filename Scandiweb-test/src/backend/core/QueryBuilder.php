<?php /** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection SqlWithoutWhere */

abstract class QueryBuilder
{
    private mysqli $db;
    private string $query = '';
    private $table_name;
    private $data = array();

    function __construct($table_name)
    {
        $this->db = (new Database)->get();
        $this->table_name = $table_name;
    }

    public function select(array $columns): static
    {
        $this->query = 'SELECT '.implode(',', $columns).' FROM '.$this->table_name;
        return $this;
    }

    public function where(string $column, string $operator, string $value): static
    {
        $this->data[] = $value;

        $this->query .= ' WHERE '.$column.' '.$operator.' ?';
        return $this;
    }

    public function insert(array $data): bool|mysqli_stmt
    {
        $this->data = array_merge($this->data, $data);

        $this->query = 'INSERT INTO '.$this->table_name.' VALUES ('.implode(',', array_fill(0, count($data), '?')).')';

        return $this->bind();
    }

    private function bind(): bool|mysqli_stmt
    {
        $stmt = $this->db->prepare($this->query);

        if($this->data != null)
        {
            $params = array();
            $params[] = $this->getTypes();
            $params = array_merge($params, $this->data);
            foreach($params as $ignored)

            call_user_func_array(array($stmt, 'bind_param'), $params);
        }
        
        $this->data = array();
        $stmt->execute();
        return $stmt;
    }

    public function get(): array
    {
        return mysqli_fetch_all($this->bind()->get_result(), MYSQLI_ASSOC);
    }

    private function getTypes(): string
    {
        $types = '';

        foreach ($this->data as $value) {
            $types .= $this->getDataType($value);
        }
        
        return $types;
    }

    private function getDataType($data): string
    {
        return match (gettype($data)) {
            'double' => 'd',
            'integer' => 'i',
            default => 's',
        };
    }

}


