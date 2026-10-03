<?php
// ─── Resident Controller ──────────────────────────────────────────────────────
class ResidentController {
    private Resident $residentModel;
    private Barangay $barangayModel;

    public function __construct() {
        $this->residentModel = new Resident();
        $this->barangayModel = new Barangay();
    }

    /** Helper to get scoped barangay ID for barangay staff */
    private function getScopedBarangayId(): ?int {
        if (userRole() === 'barangay_staff') {
            if (!empty($_SESSION['barangay_id'])) {
                return (int) $_SESSION['barangay_id'];
            }
            $assigned = $this->barangayModel->findByStaff((int) $_SESSION['user_id']);
            if ($assigned) {
                $_SESSION['barangay_id'] = $assigned['id'];
                return (int) $assigned['id'];
            }
        }
        return null; // Admin sees all unless filtered
    }

    /** List residents */
    public function index(): void {
        requireAuth();
        requireRole(['admin', 'barangay_staff']);

        $scopedBid = $this->getScopedBarangayId();
        $filterBid = $scopedBid ?? (!empty($_GET['barangay_id']) ? (int) $_GET['barangay_id'] : null);

        $filters = [
            'gender'        => $_GET['gender'] ?? '',
            'civil_status'  => $_GET['civil_status'] ?? '',
            'is_vulnerable' => $_GET['is_vulnerable'] ?? '',
            'search'        => trim($_GET['search'] ?? ''),
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $residents = $this->residentModel->getAll($filterBid, $filters, $page, 15);
        $stats     = $this->residentModel->getStats($filterBid);
        $barangays = $this->barangayModel->getList();

        include BASE_PATH . '/views/residents/index.php';
    }

    /** View resident profile details */
    public function view(): void {
        requireAuth();
        requireRole(['admin', 'barangay_staff']);

        $id = (int) ($_GET['id'] ?? 0);
        $resident = $this->residentModel->findById($id);

        if (!$resident) {
            setFlash('error', 'Resident profile not found.');
            redirect('/residents');
        }

        // Staff scope check
        $scopedBid = $this->getScopedBarangayId();
        if ($scopedBid && (int) $resident['barangay_id'] !== $scopedBid) {
            setFlash('error', 'Unauthorized access to this resident profile.');
            redirect('/residents');
        }

        include BASE_PATH . '/views/residents/view.php';
    }

    /** Create a new resident */
    public function create(): void {
        requireAuth();
        requireRole(['admin', 'barangay_staff']);
        validateCsrf();

        $scopedBid = $this->getScopedBarangayId();
        $barangayId = $scopedBid ?? (int) ($_POST['barangay_id'] ?? 1) ?: 1;

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $gender    = $_POST['gender'] ?? '';
        $birthDate = $_POST['birth_date'] ?? '';
        $phone     = trim($_POST['contact_number'] ?? '');
        $purok     = trim($_POST['purok'] ?? '');
        $rawAddr   = trim($_POST['address'] ?? '');
        $address   = $purok ? ($purok . ($rawAddr ? ' - ' . $rawAddr : '')) : $rawAddr;

        if (!$firstName || !$lastName || !$gender || !$birthDate || !$phone || !$address) {
            setFlash('error', 'Please fill in all required fields.');
            redirect('/residents');
        }

        $data = [
            'barangay_id'             => $barangayId,
            'first_name'              => $firstName,
            'middle_name'             => trim($_POST['middle_name'] ?? ''),
            'last_name'               => $lastName,
            'suffix'                  => trim($_POST['suffix'] ?? ''),
            'gender'                  => $gender,
            'birth_date'              => $birthDate,
            'civil_status'            => $_POST['civil_status'] ?? 'single',
            'contact_number'          => $phone,
            'email'                   => trim($_POST['email'] ?? ''),
            'address'                 => $address,
            'is_vulnerable'           => !empty($_POST['is_vulnerable']) ? 1 : 0,
            'vulnerability_type'      => trim($_POST['vulnerability_type'] ?? ''),
            'emergency_contact_name'  => trim($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => trim($_POST['emergency_contact_phone'] ?? ''),
            'occupation'              => trim($_POST['occupation'] ?? ''),
            'created_by'              => (int) $_SESSION['user_id'],
        ];

        $resId = $this->residentModel->create($data);
        if ($resId) {
            setFlash('success', "Resident profile for {$firstName} {$lastName} created successfully.");
        } else {
            setFlash('error', 'Failed to save resident record.');
        }

        redirect('/residents');
    }

    /** Update an existing resident */
    public function update(): void {
        requireAuth();
        requireRole(['admin', 'barangay_staff']);
        validateCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $resident = $this->residentModel->findById($id);

        if (!$resident) {
            setFlash('error', 'Resident profile not found.');
            redirect('/residents');
        }

        $scopedBid = $this->getScopedBarangayId();
        if ($scopedBid && (int) $resident['barangay_id'] !== $scopedBid) {
            setFlash('error', 'Unauthorized access to this resident profile.');
            redirect('/residents');
        }

        $barangayId = $scopedBid ?? (int) ($_POST['barangay_id'] ?? $resident['barangay_id']);

        $data = [
            'barangay_id'             => $barangayId,
            'first_name'              => trim($_POST['first_name'] ?? $resident['first_name']),
            'middle_name'             => trim($_POST['middle_name'] ?? ''),
            'last_name'               => trim($_POST['last_name'] ?? $resident['last_name']),
            'suffix'                  => trim($_POST['suffix'] ?? ''),
            'gender'                  => $_POST['gender'] ?? $resident['gender'],
            'birth_date'              => $_POST['birth_date'] ?? $resident['birth_date'],
            'civil_status'            => $_POST['civil_status'] ?? $resident['civil_status'],
            'contact_number'          => trim($_POST['contact_number'] ?? $resident['contact_number']),
            'email'                   => trim($_POST['email'] ?? ''),
            'address'                 => trim($_POST['address'] ?? $resident['address']),
            'is_vulnerable'           => !empty($_POST['is_vulnerable']) ? 1 : 0,
            'vulnerability_type'      => trim($_POST['vulnerability_type'] ?? ''),
            'emergency_contact_name'  => trim($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => trim($_POST['emergency_contact_phone'] ?? ''),
            'occupation'              => trim($_POST['occupation'] ?? ''),
        ];

        if ($this->residentModel->update($id, $data)) {
            setFlash('success', 'Resident profile updated successfully.');
        } else {
            setFlash('error', 'Failed to update resident record.');
        }

        redirect("/residents/view?id={$id}");
    }

    /** Delete a resident */
    public function delete(): void {
        requireAuth();
        requireRole(['admin', 'barangay_staff']);
        validateCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $resident = $this->residentModel->findById($id);

        if (!$resident) {
            setFlash('error', 'Resident profile not found.');
            redirect('/residents');
        }

        $scopedBid = $this->getScopedBarangayId();
        if ($scopedBid && (int) $resident['barangay_id'] !== $scopedBid) {
            setFlash('error', 'Unauthorized to delete this record.');
            redirect('/residents');
        }

        if ($this->residentModel->delete($id)) {
            setFlash('success', 'Resident profile deleted.');
        } else {
            setFlash('error', 'Failed to delete resident record.');
        }

        redirect('/residents');
    }

    /** Export resident roster to CSV */
    public function exportCsv(): void {
        requireAuth();
        requireRole(['admin', 'barangay_staff']);

        $scopedBid = $this->getScopedBarangayId();
        $filterBid = $scopedBid ?? (!empty($_GET['barangay_id']) ? (int) $_GET['barangay_id'] : null);

        $result = $this->residentModel->getAll($filterBid, [], 1, 10000);
        $data = $result['data'];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="safeher_residents_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'First Name', 'Middle Name', 'Last Name', 'Gender', 'Birth Date', 'Age', 'Civil Status', 'Barangay', 'Contact', 'Email', 'Address', 'Vulnerable Group', 'Emergency Contact', 'Emergency Phone']);

        foreach ($data as $r) {
            fputcsv($out, [
                $r['id'],
                $r['first_name'],
                $r['middle_name'],
                $r['last_name'],
                ucfirst($r['gender']),
                $r['birth_date'],
                $r['age'],
                ucfirst($r['civil_status']),
                $r['barangay_name'],
                $r['contact_number'],
                $r['email'],
                $r['address'],
                $r['is_vulnerable'] ? ($r['vulnerability_type'] ?: 'Yes') : 'No',
                $r['emergency_contact_name'],
                $r['emergency_contact_phone'],
            ]);
        }
        fclose($out);
        exit;
    }
}
