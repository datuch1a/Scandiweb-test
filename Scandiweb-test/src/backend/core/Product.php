<?php /** @noinspection PhpMissingFieldTypeInspection */

class Product extends QueryBuilder
{
    private string $table_name = 'products';

    protected $inputs;

    protected $sku;
    protected $name;
    protected $price;
    protected $type;
    protected $attribute;

    function __construct()
    {
        parent::__construct($this->table_name);
    }

    public function find(string $sku): array
    {
        return $this->select(['*'])->where('sku', '=', $sku)->get();   
    }

    public function save(): bool|mysqli_stmt
    {
        return $this->insert(array($this->sku, $this->name, $this->price, $this->type, $this->attribute));
    }

    public function getAll(): array
    {
        return $this->select(['*'])->get();
    }

    public function validateSKU(): bool
    {
        return (!preg_match('/\s/', $this->inputs['sku']) && !$this->find($this->inputs['sku']) && (strlen($this->inputs['sku']) > 0));
    }

    public function validateName(): bool
    {
        return (strlen($this->inputs['name']) > 0);
    }

    public function validatePrice(): bool
    {
        return !(filter_var($this->inputs['price'], FILTER_VALIDATE_FLOAT) && (strlen($this->inputs['price']) > 0) && floatval($this->inputs['price'] >= 0));
    }

    public function validateType(): bool
    {
        return !(preg_match('/[0-2]/', $this->inputs['type']) && (strlen($this->inputs['type']) > 0));
    }

}

