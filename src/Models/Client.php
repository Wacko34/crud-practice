<?php


class Client
{
    public int $id;
    public string $name;
    public string $email;
    public string $pasport;
    public float $salary;
    public string $credit_history;
    public string $phone_number;

    public function __construct(
        string $name,
        string $email,
        string $pasport,
        float  $salary,
        string $credit_history,
        string $phone_number,
        ?int   $id = null
    )
    {
        $this->name = $name;
        $this->email = $email;
        $this->pasport = $pasport;
        $this->salary = $salary;
        $this->credit_history = $credit_history;
        $this->phone_number = $phone_number;
        $this->id = $id ?? 0;
    }

    // Сохранение нового клиента
    public function save(): bool
    {
        if ($this->id > 0) {
            return $this->update();
        }

        $sql = "INSERT INTO Client (Name, Email, Pasport, Salary, Credit_history, Phone_number)
                VALUES (:name, :email, :pasport, :salary, :credit_history, :phone_number)";

        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':name' => $this->name,
            ':email' => $this->email,
            ':pasport' => $this->pasport,
            ':salary' => $this->salary,
            ':credit_history' => $this->credit_history,
            ':phone_number' => $this->phone_number,
        ]);
    }

    // Обновление существующего клиента
    private function update(): bool
    {
        $sql = "UPDATE Client SET
                    Name = :name,
                    Email = :email,
                    Pasport = :pasport,
                    Salary = :salary,
                    Credit_history = :credit_history,
                    Phone_number = :phone_number
                WHERE ID = :id";

        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':id' => $this->id,
            ':name' => $this->name,
            ':email' => $this->email,
            ':pasport' => $this->pasport,
            ':salary' => $this->salary,
            ':credit_history' => $this->credit_history,
            ':phone_number' => $this->phone_number,
        ]);
    }

    // Удаление клиента
    public function delete(): bool
    {
        $sql = "DELETE FROM Client WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([':id' => $this->id]);
    }

    // Найти клиента по ID
    public static function find(int $id): ?self
    {
        $sql = "SELECT * FROM Client WHERE ID = :id";
        $stmt = Database::prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new self(
            $row['Name'],
            $row['Email'],
            $row['Pasport'],
            (float)$row['Salary'],
            $row['Credit_history'],
            $row['Phone_number'],
            (int)$row['ID']
        );
    }

    // Получить всех клиентов
    public static function all(): array
    {
        $sql = "SELECT * FROM Client ORDER BY ID";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $clients = [];
        foreach ($rows as $row) {
            $clients[] = new self(
                $row['Name'],
                $row['Email'],
                $row['Pasport'],
                (float)$row['Salary'],
                $row['Credit_history'],
                $row['Phone_number'],
                (int)$row['ID']
            );
        }

        return $clients;
    }

    // Проверка уникальности email, паспорта или телефона (опционально, для валидации)
    public static function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT ID FROM Client WHERE Email = :email";
        $params = [':email' => $email];
        if ($excludeId !== null) {
            $sql .= " AND ID != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public static function existsByPasport(string $pasport, ?int $excludeId = null): bool
    {
        $sql = "SELECT ID FROM Client WHERE Pasport = :pasport";
        $params = [':pasport' => $pasport];
        if ($excludeId !== null) {
            $sql .= " AND ID != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public static function existsByPhone(string $phone, ?int $excludeId = null): bool
    {
        $sql = "SELECT ID FROM Client WHERE Phone_number = :phone";
        $params = [':phone' => $phone];
        if ($excludeId !== null) {
            $sql .= " AND ID != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }
}