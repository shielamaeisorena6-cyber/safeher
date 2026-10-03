<?php
// ─── Case Model ───────────────────────────────────────────────────────────────
class CaseModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Create a new case */
    public function create(array $data): int {
        $data['case_number'] = generateCaseNumber();
        $sql = "INSERT INTO cases
                    (case_number, complainant_id, barangay_id, type_of_violence,
                     incident_date, location, description, evidence_path,
                     status, assigned_staff_id, created_at, updated_at)
                VALUES
                    (:case_number, :complainant_id, :barangay_id, :type_of_violence,
                     :incident_date, :location, :description, :evidence_path,
                     'pending', :assigned_staff_id, NOW(), NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':case_number'      => $data['case_number'],
            ':complainant_id'   => $data['complainant_id'],
            ':barangay_id'      => $data['barangay_id'],
            ':type_of_violence' => $data['type_of_violence'],
            ':incident_date'    => $data['incident_date'],
            ':location'         => $data['location'],
            ':description'      => $data['description'],
            ':evidence_path'    => $data['evidence_path'] ?? null,
            ':assigned_staff_id'=> $data['assigned_staff_id'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Find case by ID with related data */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    u.full_name AS complainant_name, u.phone AS complainant_phone,
                    b.name AS barangay_name,
                    s.full_name AS assigned_staff_name
             FROM cases c
             LEFT JOIN users u ON c.complainant_id = u.id
             LEFT JOIN barangays b ON c.barangay_id = b.id
             LEFT JOIN users s ON c.assigned_staff_id = s.id
             WHERE c.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Find case by case_number */
    public function findByCaseNumber(string $caseNumber): ?array {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.full_name AS complainant_name, b.name AS barangay_name
             FROM cases c
             LEFT JOIN users u ON c.complainant_id = u.id
             LEFT JOIN barangays b ON c.barangay_id = b.id
             WHERE c.case_number = :cn LIMIT 1"
        );
        $stmt->execute([':cn' => $caseNumber]);
        return $stmt->fetch() ?: null;
    }

    /** Get all cases (admin) with filters */
    public function getAll(array $filters = [], int $page = 1): array {
        $where  = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "c.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['type_of_violence'])) {
            $where[] = "c.type_of_violence = :tov";
            $params[':tov'] = $filters['type_of_violence'];
        }
        if (!empty($filters['barangay_id'])) {
            $where[] = "c.barangay_id = :barangay_id";
            $params[':barangay_id'] = $filters['barangay_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(c.case_number LIKE :search OR u.full_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT c.*, u.full_name AS complainant_name, b.name AS barangay_name
                FROM cases c
                LEFT JOIN users u ON c.complainant_id = u.id
                LEFT JOIN barangays b ON c.barangay_id = b.id
                WHERE $whereClause
                ORDER BY c.created_at DESC";

        return paginate($sql, $params, $page);
    }

    /** Get cases for a complainant */
    public function getForComplainant(int $userId, int $page = 1): array {
        $sql = "SELECT c.*, b.name AS barangay_name
                FROM cases c
                LEFT JOIN barangays b ON c.barangay_id = b.id
                WHERE c.complainant_id = :uid
                ORDER BY c.created_at DESC";
        return paginate($sql, [':uid' => $userId], $page);
    }

    /** Get cases for a barangay */
    public function getForBarangay(int $barangayId, array $filters = [], int $page = 1): array {
        $where  = ["c.barangay_id = :barangay_id"];
        $params = [':barangay_id' => $barangayId];

        if (!empty($filters['status'])) {
            $where[] = "c.status = :status";
            $params[':status'] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT c.*, u.full_name AS complainant_name
                FROM cases c
                LEFT JOIN users u ON c.complainant_id = u.id
                WHERE $whereClause
                ORDER BY c.created_at DESC";

        return paginate($sql, $params, $page);
    }

    /** Update case status */
    public function updateStatus(int $id, string $status, string $remarks, int $staffId): bool {
        $case = $this->findById($id);
        if (!$case) return false;

        // Log the update
        $logStmt = $this->db->prepare(
            "INSERT INTO case_updates (case_id, updated_by, old_status, new_status, remarks, updated_at)
             VALUES (:cid, :uid, :old, :new, :remarks, NOW())"
        );
        $logStmt->execute([
            ':cid'     => $id,
            ':uid'     => $staffId,
            ':old'     => $case['status'],
            ':new'     => $status,
            ':remarks' => $remarks,
        ]);

        // Update the case
        $stmt = $this->db->prepare(
            "UPDATE cases SET status = :status, remarks = :remarks, updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':remarks' => $remarks, ':id' => $id]);
    }

    /** Get case history/updates */
    public function getUpdates(int $caseId): array {
        $stmt = $this->db->prepare(
            "SELECT cu.*, u.full_name AS updated_by_name
             FROM case_updates cu
             LEFT JOIN users u ON cu.updated_by = u.id
             WHERE cu.case_id = :cid
             ORDER BY cu.updated_at ASC"
        );
        $stmt->execute([':cid' => $caseId]);
        return $stmt->fetchAll();
    }

    /** Count cases by status */
    public function countByStatus(?int $barangayId = null): array {
        $where  = $barangayId ? "WHERE barangay_id = :bid" : "";
        $params = $barangayId ? [':bid' => $barangayId] : [];
        $stmt   = $this->db->prepare(
            "SELECT status, COUNT(*) AS total FROM cases $where GROUP BY status"
        );
        $stmt->execute($params);
        $result = ['pending'=>0,'under_investigation'=>0,'resolved'=>0,'dismissed'=>0,'total'=>0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int) $row['total'];
            $result['total'] += (int) $row['total'];
        }
        return $result;
    }

    /** Count cases by violence type */
    public function countByViolenceType(): array {
        $stmt = $this->db->query(
            "SELECT type_of_violence, COUNT(*) AS total FROM cases GROUP BY type_of_violence"
        );
        return $stmt->fetchAll();
    }

    /** Monthly case counts for the past N months */
    public function monthlyTrend(int $months = 12): array {
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
             FROM cases
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL :m MONTH)
             GROUP BY month
             ORDER BY month ASC"
        );
        $stmt->execute([':m' => $months]);
        return $stmt->fetchAll();
    }

    /** Cases per barangay */
    public function countByBarangay(): array {
        $stmt = $this->db->query(
            "SELECT b.name AS barangay, COUNT(c.id) AS total
             FROM barangays b
             LEFT JOIN cases c ON c.barangay_id = b.id
             GROUP BY b.id, b.name
             ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }

    /** Cases per Purok / Zone in Barangay Gogon Centro */
    public function countByPurok(): array {
        $puroks = defined('BARANGAY_PUROKS') ? BARANGAY_PUROKS : ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Riverside', 'Centro Proper'];
        $stmt   = $this->db->query("SELECT location FROM cases");
        $locations = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

        $counts = array_fill_keys($puroks, 0);

        foreach ($locations as $loc) {
            $matched = false;
            foreach ($puroks as $p) {
                if (stripos((string)$loc, $p) !== false) {
                    $counts[$p]++;
                    $matched = true;
                    break;
                }
            }
            if (!$matched && !empty($loc)) {
                $counts['Other'] = ($counts['Other'] ?? 0) + 1;
            }
        }

        $result = [];
        foreach ($counts as $purok => $total) {
            $result[] = ['purok' => $purok, 'total' => $total];
        }
        usort($result, fn($a, $b) => $b['total'] <=> $a['total']);
        return $result;
    }

    /** Delete a case (admin only) */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM cases WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
