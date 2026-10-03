<?php
// ─── Notification Model ───────────────────────────────────────────────────────
class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Create a notification record */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, case_id, recipient_phone, message, sms_status, sent_at, created_at)
             VALUES (:user_id, :case_id, :phone, :message, :status, NOW(), NOW())"
        );
        $stmt->execute([
            ':user_id' => $data['user_id'] ?? null,
            ':case_id' => $data['case_id'] ?? null,
            ':phone'   => $data['recipient_phone'],
            ':message' => $data['message'],
            ':status'  => $data['sms_status'] ?? 'pending',
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Get notifications with filters */
    public function getAll(int $page = 1): array {
        $sql = "SELECT n.*, u.full_name AS user_name, c.case_number
                FROM notifications n
                LEFT JOIN users u ON n.user_id = u.id
                LEFT JOIN cases c ON n.case_id = c.id
                ORDER BY n.created_at DESC";
        return paginate($sql, [], $page);
    }

    /** Get notifications for a user */
    public function getForUser(int $userId, int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count by SMS status */
    public function countByStatus(): array {
        $stmt = $this->db->query(
            "SELECT sms_status, COUNT(*) AS total FROM notifications GROUP BY sms_status"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['sms_status']] = (int) $row['total'];
        }
        return $result;
    }

    /** Total notifications sent */
    public function totalSent(): int {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM notifications WHERE sms_status = 'sent'"
        )->fetchColumn();
    }
}
