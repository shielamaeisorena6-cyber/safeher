<?php
// ─── Resident Model ───────────────────────────────────────────────────────────
class Resident {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Create a new resident */
    public function create(array $data): int {
        $sql = "INSERT INTO residents
                (barangay_id, first_name, middle_name, last_name, suffix, gender,
                 birth_date, civil_status, contact_number, email, address,
                 is_vulnerable, vulnerability_type, emergency_contact_name,
                 emergency_contact_phone, occupation, created_by, created_at, updated_at)
                VALUES
                (:barangay_id, :first_name, :middle_name, :last_name, :suffix, :gender,
                 :birth_date, :civil_status, :contact_number, :email, :address,
                 :is_vulnerable, :vulnerability_type, :emergency_contact_name,
                 :emergency_contact_phone, :occupation, :created_by, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':barangay_id'              => $data['barangay_id'],
            ':first_name'               => $data['first_name'],
            ':middle_name'              => $data['middle_name'] ?? null,
            ':last_name'                => $data['last_name'],
            ':suffix'                   => $data['suffix'] ?? null,
            ':gender'                   => $data['gender'],
            ':birth_date'               => $data['birth_date'],
            ':civil_status'             => $data['civil_status'],
            ':contact_number'           => $data['contact_number'],
            ':email'                    => $data['email'] ?? null,
            ':address'                  => $data['address'],
            ':is_vulnerable'            => !empty($data['is_vulnerable']) ? 1 : 0,
            ':vulnerability_type'       => $data['vulnerability_type'] ?? null,
            ':emergency_contact_name'   => $data['emergency_contact_name'] ?? null,
            ':emergency_contact_phone'  => $data['emergency_contact_phone'] ?? null,
            ':occupation'               => $data['occupation'] ?? null,
            ':created_by'               => $data['created_by'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** Find resident by ID */
    public function findById(int $id): ?array {
        $sql = "SELECT r.*, b.name AS barangay_name, b.municipality AS barangay_municipality,
                       u.full_name AS encoder_name
                FROM residents r
                LEFT JOIN barangays b ON r.barangay_id = b.id
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Get all residents with optional barangay and search filters */
    public function getAll(?int $barangayId = null, array $filters = [], int $page = 1, int $perPage = 15): array {
        $where = ["1=1"];
        $params = [];

        if ($barangayId !== null) {
            $where[] = "r.barangay_id = :bid";
            $params[':bid'] = $barangayId;
        }

        if (!empty($filters['gender'])) {
            $where[] = "r.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }

        if (!empty($filters['civil_status'])) {
            $where[] = "r.civil_status = :civil_status";
            $params[':civil_status'] = $filters['civil_status'];
        }

        if (isset($filters['is_vulnerable']) && $filters['is_vulnerable'] !== '') {
            $where[] = "r.is_vulnerable = :is_vulnerable";
            $params[':is_vulnerable'] = (int) $filters['is_vulnerable'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(r.first_name LIKE :s OR r.last_name LIKE :s OR r.contact_number LIKE :s OR r.address LIKE :s)";
            $params[':s'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT r.*, b.name AS barangay_name,
                       TIMESTAMPDIFF(YEAR, r.birth_date, CURDATE()) AS age
                FROM residents r
                LEFT JOIN barangays b ON r.barangay_id = b.id
                WHERE $whereClause
                ORDER BY r.last_name ASC, r.first_name ASC";

        return paginate($sql, $params, $page, $perPage);
    }

    /** Update a resident */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE residents SET
                barangay_id = :barangay_id,
                first_name = :first_name,
                middle_name = :middle_name,
                last_name = :last_name,
                suffix = :suffix,
                gender = :gender,
                birth_date = :birth_date,
                civil_status = :civil_status,
                contact_number = :contact_number,
                email = :email,
                address = :address,
                is_vulnerable = :is_vulnerable,
                vulnerability_type = :vulnerability_type,
                emergency_contact_name = :emergency_contact_name,
                emergency_contact_phone = :emergency_contact_phone,
                occupation = :occupation,
                updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':barangay_id'              => $data['barangay_id'],
            ':first_name'               => $data['first_name'],
            ':middle_name'              => $data['middle_name'] ?? null,
            ':last_name'                => $data['last_name'],
            ':suffix'                   => $data['suffix'] ?? null,
            ':gender'                   => $data['gender'],
            ':birth_date'               => $data['birth_date'],
            ':civil_status'             => $data['civil_status'],
            ':contact_number'           => $data['contact_number'],
            ':email'                    => $data['email'] ?? null,
            ':address'                  => $data['address'],
            ':is_vulnerable'            => !empty($data['is_vulnerable']) ? 1 : 0,
            ':vulnerability_type'       => $data['vulnerability_type'] ?? null,
            ':emergency_contact_name'   => $data['emergency_contact_name'] ?? null,
            ':emergency_contact_phone'  => $data['emergency_contact_phone'] ?? null,
            ':occupation'               => $data['occupation'] ?? null,
            ':id'                       => $id,
        ]);
    }

    /** Delete a resident */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM residents WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Summary statistics for resident profiling */
    public function getStats(?int $barangayId = null): array {
        $where = $barangayId ? "WHERE barangay_id = :bid" : "";
        $params = $barangayId ? [':bid' => $barangayId] : [];

        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM residents $where");
        $totalStmt->execute($params);
        $total = (int) $totalStmt->fetchColumn();

        $femaleStmt = $this->db->prepare("SELECT COUNT(*) FROM residents $where " . ($where ? "AND" : "WHERE") . " gender = 'female'");
        $femaleStmt->execute($params);
        $female = (int) $femaleStmt->fetchColumn();

        $maleStmt = $this->db->prepare("SELECT COUNT(*) FROM residents $where " . ($where ? "AND" : "WHERE") . " gender = 'male'");
        $maleStmt->execute($params);
        $male = (int) $maleStmt->fetchColumn();

        $vulnStmt = $this->db->prepare("SELECT COUNT(*) FROM residents $where " . ($where ? "AND" : "WHERE") . " is_vulnerable = 1");
        $vulnStmt->execute($params);
        $vulnerable = (int) $vulnStmt->fetchColumn();

        return [
            'total'      => $total,
            'female'     => $female,
            'male'       => $male,
            'vulnerable' => $vulnerable,
        ];
    }
}
