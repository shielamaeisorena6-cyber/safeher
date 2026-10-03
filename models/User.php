<?php
// ─── User Model ───────────────────────────────────────────────────────────────
class User {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Find a user by email */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /** Find a user by ID */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Create a new user */
    public function create(array $data): int {
        $sql = "INSERT INTO users (full_name, email, phone, password_hash, role, barangay_id, status, created_at)
                VALUES (:full_name, :email, :phone, :password_hash, :role, :barangay_id, 'active', NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':full_name'     => $data['full_name'],
            ':email'         => $data['email'],
            ':phone'         => $data['phone'],
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':role'          => $data['role'],
            ':barangay_id'   => $data['barangay_id'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Update a user */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $sql  = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /** Update password */
    public function updatePassword(int $id, string $newPassword): bool {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        return $stmt->execute([':hash' => password_hash($newPassword, PASSWORD_BCRYPT), ':id' => $id]);
    }

    /** Get all users with optional role filter */
    public function getAll(?string $role = null, int $page = 1): array {
        $where  = $role ? "WHERE role = :role" : "";
        $params = $role ? [':role' => $role] : [];
        $sql    = "SELECT u.*, b.name AS barangay_name
                   FROM users u
                   LEFT JOIN barangays b ON u.barangay_id = b.id
                   $where ORDER BY u.created_at DESC";
        return paginate($sql, $params, $page);
    }

    /** Count users by role */
    public function countByRole(): array {
        $stmt = $this->db->query(
            "SELECT role, COUNT(*) AS total FROM users GROUP BY role"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['role']] = (int) $row['total'];
        }
        return $result;
    }

    /** Authenticate user */
    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if ($user['status'] !== 'active') return null;
        if (!password_verify($password, $user['password_hash'])) return null;
        return $user;
    }

    /** Toggle user status */
    public function toggleStatus(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET status = IF(status='active','inactive','active') WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /** Get all phone numbers for bulk SMS */
    public function getAllPhones(?string $role = null): array {
        $where  = $role ? "WHERE role = :role AND status = 'active'" : "WHERE status = 'active'";
        $params = $role ? [':role' => $role] : [];
        $stmt   = $this->db->prepare("SELECT phone FROM users $where");
        $stmt->execute($params);
        return array_column($stmt->fetchAll(), 'phone');
    }

    /** Delete a user */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
