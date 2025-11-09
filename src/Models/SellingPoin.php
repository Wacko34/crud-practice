<?php


class SellingPoint
{
    public int $id;
    public string $name;
    public string $adress;      // ⚠️ Опечатка в названии поля — см. примечание ниже
    public string $work_schedule;
    public string $phone_number;

    public function __construct(
        string $name,
        string $adress,
        string $work_schedule,
        string $phone_number,
        ?int   $id = null
    )
    {
        $this->name = $name;
        $this->adress = $adress;           // сохраняем как в БД
        $this->work_schedule = $work_schedule;
        $this->phone_number = $phone_number;
        $this->id = $id ?? 0;
    }

    public function save(): bool
    {
        if ($this->id > 0) {
            return $this->update();
        }

        $sql = "INSERT INTO Selling_point (Name, Adress, Work_schedule, Phone_number)
                VALUES (:name, :adress, :work_schedule, :phone_number)";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':name' => $this->name,
            ':adress' => $this->adress,
            ':work_schedule' => $this->work_schedule,
            ':phone_number' => $this->phone_number,
        ]);
    }

    private function update(): bool
    {
        $sql = "UPDATE Selling_point SET
                    Name = :name,
                    Adress = :adress,
                    Work_schedule = :work_schedule,
                    Phone_number = :phone_number
                WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':id' => $this->id,
            ':name' => $this->name,
            ':adress' => $this->adress,
            ':work_schedule' => $this->work_schedule,
            ':phone_number' => $this->phone_number,
        ]);
    }

    public function delete(): bool
    {
        // Проверим, есть ли привязанные менеджеры
//        if (Manager::existsBySellingPointId($this->id)) {
//            throw new RuntimeException("Нельзя удалить торговую точку: к ней привязаны менеджеры.");
//        }

        $sql = "DELETE FROM Selling_point WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([':id' => $this->id]);
    }

    public static function find(int $id): ?self
    {
        $sql = "SELECT * FROM Selling_point WHERE ID = :id";
        $stmt = Database::prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        return new self(
            $row['Name'],
            $row['Adress'],
            $row['Work_schedule'],
            $row['Phone_number'],
            (int)$row['ID']
        );
    }

    public static function all(): array
    {
        $sql = "SELECT * FROM Selling_point ORDER BY Name";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $points = [];
        foreach ($rows as $row) {
            $points[] = new self(
                $row['Name'],
                $row['Adress'],
                $row['Work_schedule'],
                $row['Phone_number'],
                (int)$row['ID']
            );
        }
        return $points;
    }

    // Проверка уникальности
    public static function existsByName(string $name, ?int $excludeId = null): bool
    {
        return self::existsByField('Name', $name, $excludeId);
    }

    public static function existsByAdress(string $adress, ?int $excludeId = null): bool
    {
        return self::existsByField('Adress', $adress, $excludeId);
    }

    public static function existsByPhoneNumber(string $phone, ?int $excludeId = null): bool
    {
        return self::existsByField('Phone_number', $phone, $excludeId);
    }

    private static function existsByField(string $field, string $value, ?int $excludeId = null): bool
    {
        $sql = "SELECT ID FROM Selling_point WHERE `$field` = :value";
        $params = [':value' => $value];
        if ($excludeId !== null) {
            $sql .= " AND ID != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }
}