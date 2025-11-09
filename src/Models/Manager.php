<?php


class Manager
{
    public int $id;
    public string $name;
    public string $pasport;
    public int $selling_point_id;

    public function __construct(
        string $name,
        string $pasport,
        int    $selling_point_id,
        ?int   $id = null
    )
    {
        $this->name = $name;
        $this->pasport = $pasport;
        $this->selling_point_id = $selling_point_id;
        $this->id = $id ?? 0;
    }

    public function save(): bool
    {
        if ($this->id > 0) {
            return $this->update();
        }

        $sql = "INSERT INTO Manager (Name, Pasport, Selling_point_ID)
                VALUES (:name, :pasport, :selling_point_id)";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':name' => $this->name,
            ':pasport' => $this->pasport,
            ':selling_point_id' => $this->selling_point_id,
        ]);
    }

    private function update(): bool
    {
        $sql = "UPDATE Manager SET
                    Name = :name,
                    Pasport = :pasport,
                    Selling_point_ID = :selling_point_id
                WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':id' => $this->id,
            ':name' => $this->name,
            ':pasport' => $this->pasport,
            ':selling_point_id' => $this->selling_point_id,
        ]);
    }

    public function delete(): bool
    {
        // Сначала удалим связь в Loan_manager
        LoanManager::deleteByManagerId($this->id);

        $sql = "DELETE FROM Manager WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([':id' => $this->id]);
    }

    public static function find(int $id): ?self
    {
        $sql = "SELECT * FROM Manager WHERE ID = :id";
        $stmt = Database::prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        return new self(
            $row['Name'],
            $row['Pasport'],
            (int)$row['Selling_point_ID'],
            (int)$row['ID']
        );
    }

    public static function all(): array
    {
        $sql = "SELECT * FROM Manager ORDER BY Name";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $managers = [];
        foreach ($rows as $row) {
            $managers[] = new self(
                $row['Name'],
                $row['Pasport'],
                (int)$row['Selling_point_ID'],
                (int)$row['ID']
            );
        }
        return $managers;
    }

    // Уникальность паспорта
    public static function existsByPasport(string $pasport, ?int $excludeId = null): bool
    {
        $sql = "SELECT ID FROM Manager WHERE Pasport = :pasport";
        $params = [':pasport' => $pasport];
        if ($excludeId !== null) {
            $sql .= " AND ID != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    // Получить все кредиты, которые обслуживает менеджер
    public function getLoans(): array
    {
        return LoanManager::getLoansByManagerId($this->id);
    }

    // Назначить кредит менеджеру
    public function assignLoan(int $loanId): bool
    {
        return LoanManager::assign($this->id, $loanId);
    }

    // Отвязать кредит от менеджера
    public function unassignLoan(int $loanId): bool
    {
        return LoanManager::unassign($this->id, $loanId);
    }
}