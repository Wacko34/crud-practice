<?php


class Chargeback
{
    public int $id;
    public float $amount;
    public string $date; // формат 'Y-m-d H:i:s'
    public int $loan_id;

    public function __construct(
        float  $amount,
        string $date,
        int    $loan_id,
        ?int   $id = null
    )
    {
        $this->amount = $amount;
        $this->date = $date;
        $this->loan_id = $loan_id;
        $this->id = $id ?? 0;
    }

    public function save(): bool
    {
        if ($this->id > 0) {
            return $this->update();
        }

        $sql = "INSERT INTO Chargeback (Amount, Date, Loan_ID)
                VALUES (:amount, :date, :loan_id)";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':amount' => $this->amount,
            ':date' => $this->date,
            ':loan_id' => $this->loan_id,
        ]);
    }

    private function update(): bool
    {
        $sql = "UPDATE Chargeback SET
                    Amount = :amount,
                    Date = :date,
                    Loan_ID = :loan_id
                WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':id' => $this->id,
            ':amount' => $this->amount,
            ':date' => $this->date,
            ':loan_id' => $this->loan_id,
        ]);
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM Chargeback WHERE ID = :id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([':id' => $this->id]);
    }

    public static function find(int $id): ?self
    {
        $sql = "SELECT * FROM Chargeback WHERE ID = :id";
        $stmt = Database::prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new self(
            (float)$row['Amount'],
            $row['Date'],
            (int)$row['Loan_ID'],
            (int)$row['ID']
        );
    }

    public static function all(): array
    {
        $sql = "SELECT * FROM Chargeback ORDER BY Date DESC";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $chargebacks = [];
        foreach ($rows as $row) {
            $chargebacks[] = new self(
                (float)$row['Amount'],
                $row['Date'],
                (int)$row['Loan_ID'],
                (int)$row['ID']
            );
        }
        return $chargebacks;
    }

    // Все чарджбэки по кредиту
    public static function findByLoanId(int $loanId): array
    {
        $sql = "SELECT * FROM Chargeback WHERE Loan_ID = :loan_id ORDER BY Date DESC";
        $stmt = Database::prepare($sql);
        $stmt->execute([':loan_id' => $loanId]);
        $rows = $stmt->fetchAll();

        $chargebacks = [];
        foreach ($rows as $row) {
            $chargebacks[] = new self(
                (float)$row['Amount'],
                $row['Date'],
                (int)$row['Loan_ID'],
                (int)$row['ID']
            );
        }
        return $chargebacks;
    }

    // Проверка: существует ли чарджбэк для кредита (опционально)
    public static function existsForLoan(int $loanId): bool
    {
        $sql = "SELECT 1 FROM Chargeback WHERE Loan_ID = :loan_id LIMIT 1";
        $stmt = Database::prepare($sql);
        $stmt->execute([':loan_id' => $loanId]);
        return (bool)$stmt->fetch();
    }
}