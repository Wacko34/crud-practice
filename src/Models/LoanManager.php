<?php


class LoanManager
{
    // Назначить кредит менеджеру
    public static function assign(int $managerId, int $loanId): bool
    {
        // Проверим, не назначено ли уже
        if (self::isAssigned($managerId, $loanId)) {
            return true; // или false — по желанию
        }

        $sql = "INSERT INTO Loan_manager (Manager_ID, Loan_ID) VALUES (:manager_id, :loan_id)";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':manager_id' => $managerId,
            ':loan_id' => $loanId,
        ]);
    }

    // Отвязать кредит от менеджера
    public static function unassign(int $managerId, int $loanId): bool
    {
        $sql = "DELETE FROM Loan_manager WHERE Manager_ID = :manager_id AND Loan_ID = :loan_id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([
            ':manager_id' => $managerId,
            ':loan_id' => $loanId,
        ]);
    }

    // Проверить, назначен ли кредит менеджеру
    public static function isAssigned(int $managerId, int $loanId): bool
    {
        $sql = "SELECT 1 FROM Loan_manager WHERE Manager_ID = :manager_id AND Loan_ID = :loan_id";
        $stmt = Database::prepare($sql);
        $stmt->execute([
            ':manager_id' => $managerId,
            ':loan_id' => $loanId,
        ]);
        return (bool)$stmt->fetch();
    }

    // Получить все кредиты, связанные с менеджером
    public static function getLoansByManagerId(int $managerId): array
    {
        $sql = "
            SELECT l.* 
            FROM Loan l
            INNER JOIN Loan_manager lm ON l.ID = lm.Loan_ID
            WHERE lm.Manager_ID = :manager_id
            ORDER BY l.Term DESC
        ";
        $stmt = Database::prepare($sql);
        $stmt->execute([':manager_id' => $managerId]);
        $rows = $stmt->fetchAll();

        $loans = [];
        foreach ($rows as $row) {
            $loans[] = new Loan(
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

    // Получить всех менеджеров по кредиту
    public static function getManagersByLoanId(int $loanId): array
    {
        $sql = "
            SELECT m.* 
            FROM Manager m
            INNER JOIN Loan_manager lm ON m.ID = lm.Manager_ID
            WHERE lm.Loan_ID = :loan_id
        ";
        $stmt = Database::prepare($sql);
        $stmt->execute([':loan_id' => $loanId]);
        $rows = $stmt->fetchAll();

        $managers = [];
        foreach ($rows as $row) {
            $managers[] = new Manager(
                $row['Name'],
                $row['Pasport'],
                (int)$row['Selling_point_ID'],
                (int)$row['ID']
            );
        }
        return $managers;
    }

    // Удалить все связи менеджера (при удалении менеджера)
    public static function deleteByManagerId(int $managerId): bool
    {
        $sql = "DELETE FROM Loan_manager WHERE Manager_ID = :manager_id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([':manager_id' => $managerId]);
    }

    // Удалить все связи кредита (при удалении кредита)
    public static function deleteByLoanId(int $loanId): bool
    {
        $sql = "DELETE FROM Loan_manager WHERE Loan_ID = :loan_id";
        $stmt = Database::prepare($sql);
        return $stmt->execute([':loan_id' => $loanId]);
    }
}