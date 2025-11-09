<?php


class Loan
{
    public int $id;
    public float $amount;
    public string $term; // хранится как строка в формате 'Y-m-d H:i:s'
    public int $percent;
    public int $client_id;
    public float $penalty;

    public function __construct(
        float  $amount,
        string $term,
        int    $percent,
        int    $client_id,
        float  $penalty,
        ?int   $id = null
    )
    {
        $this->amount = $amount;
        $this->term = $term;
        $this->percent = $percent;
        $this->client_id = $client_id;
        $this->penalty = $penalty;
        $this->id = $id ?? 0;
    }

    // Сохранение (INSERT или UPDATE)
    public function save(): bool
    {
        if ($this->id > 0) {
            return $this->update();
        }

        $sql = "INSERT INTO Loan (Amount, Term, Percent, Client_ID, Penalty)
                VALUES (:amount, :term, :percent, :client_id, :penalty)";

        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':amount' => $this->amount,
            ':term' => $this->term,
            ':percent' => $this->percent,
            ':client_id' => $this->client_id,
            ':penalty' => $this->penalty,
        ]);
    }

    // Обновление существующего кредита
    private function update(): bool
    {
        $sql = "UPDATE Loan SET
                    Amount = :amount,
                    Term = :term,
                    Percent = :percent,
                    Client_ID = :client_id,
                    Penalty = :penalty
                WHERE ID = :id";

        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':id' => $this->id,
            ':amount' => $this->amount,
            ':term' => $this->term,
            ':percent' => $this->percent,
            ':client_id' => $this->client_id,
            ':penalty' => $this->penalty,
        ]);
    }

    // Удаление
    public function delete(): bool
    {
        $sql = "DELETE FROM Loan WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([':id' => $this->id]);
    }

    // Найти по ID
    public static function find(int $id): ?self
    {
        $sql = "SELECT * FROM Loan WHERE ID = :id";
        $stmt = Database::prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new self(
            (float)$row['Amount'],
            $row['Term'], // DATETIME сохраняется как строка
            (int)$row['Percent'],
            (int)$row['Client_ID'],
            (float)$row['Penalty'],
            (int)$row['ID']
        );
    }

    // Все кредиты
    public static function all(): array
    {
        $sql = "SELECT * FROM Loan ORDER BY ID";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $loans = [];
        foreach ($rows as $row) {
            $loans[] = new self(
                (float)$row['Amount'],
                $row['Term'],
                (int)$row['Percent'],
                (int)$row['Client_ID'],
                (float)$row['Penalty'],
                (int)$row['ID']
            );
        }
        return $loans;
    }

    // Кредиты конкретного клиента
    public static function findByClientId(int $client_id): array
    {
        $sql = "SELECT * FROM Loan WHERE Client_ID = :client_id ORDER BY Term DESC";
        $stmt = Database::prepare($sql);
        $stmt->execute([':client_id' => $client_id]);
        $rows = $stmt->fetchAll();

        $loans = [];
        foreach ($rows as $row) {
            $loans[] = new self(
                (float)$row['Amount'],
                $row['Term'],
                (int)$row['Percent'],
                (int)$row['Client_ID'],
                (float)$row['Penalty'],
                (int)$row['ID']
            );
        }
        return $loans;
    }

    // Проверка существования кредита для клиента (опционально, для бизнес-логики)
    public static function existsForClient(int $client_id): bool
    {
        $sql = "SELECT 1 FROM Loan WHERE Client_ID = :client_id LIMIT 1";
        $stmt = Database::prepare($sql);
        $stmt->execute([':client_id' => $client_id]);
        return (bool)$stmt->fetch();
    }
}