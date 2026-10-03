<?php
// ─── Barangay Model ───────────────────────────────────────────────────────────
class Barangay {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Get all barangays */
    public function getAll(int $page = 1): array {
        $sql = "SELECT b.*, u.full_name AS staff_name, u.phone AS staff_phone,
                       COUNT(c.id) AS case_count
                FROM barangays b
                LEFT JOIN users u ON b.assigned_staff_id = u.id
                LEFT JOIN cases c ON c.barangay_id = b.id
                GROUP BY b.id
                ORDER BY b.name ASC";
        return paginate($sql, [], $page);
    }

    /** Get a simple list (id, name) for dropdowns */
    public function getList(): array {
        $stmt = $this->db->query("SELECT id, name, municipality FROM barangays ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /** Find barangay by ID */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT b.*, u.full_name AS staff_name
             FROM barangays b
             LEFT JOIN users u ON b.assigned_staff_id = u.id
             WHERE b.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Find barangay assigned to a staff user */
    public function findByStaff(int $staffId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM barangays WHERE assigned_staff_id = :sid LIMIT 1"
        );
        $stmt->execute([':sid' => $staffId]);
        return $stmt->fetch() ?: null;
    }

    /** Create a barangay */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO barangays (name, municipality, province, assigned_staff_id, created_at)
             VALUES (:name, :municipality, :province, :staff_id, NOW())"
        );
        $stmt->execute([
            ':name'         => $data['name'],
            ':municipality' => $data['municipality'],
            ':province'     => $data['province'],
            ':staff_id'     => $data['assigned_staff_id'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Update a barangay */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE barangays SET name=:name, municipality=:municipality,
             province=:province, assigned_staff_id=:staff_id WHERE id=:id"
        );
        return $stmt->execute([
            ':name'         => $data['name'],
            ':municipality' => $data['municipality'],
            ':province'     => $data['province'],
            ':staff_id'     => $data['assigned_staff_id'] ?? null,
            ':id'           => $id,
        ]);
    }

    /** Delete a barangay */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM barangays WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Total barangay count */
    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM barangays")->fetchColumn();
    }
}
