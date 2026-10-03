<?php
// ─── Admin Controller ─────────────────────────────────────────────────────────
class AdminController {
    private User      $userModel;
    private CaseModel $caseModel;
    private Barangay  $barangayModel;
    private Notification $notifModel;

    public function __construct() {
        requireRole('admin');
        $this->userModel     = new User();
        $this->caseModel     = new CaseModel();
        $this->barangayModel = new Barangay();
        $this->notifModel    = new Notification();
    }

    /** GET /admin/dashboard */
    public function dashboard(): void {
        $stats    = $this->caseModel->countByStatus();
        $userCnts = $this->userModel->countByRole();
        $recent   = $this->caseModel->getAll([], 1);
        $resStats = (new Resident())->getStats();

        $this->render('admin/dashboard', [
            'stats'       => $stats,
            'userCounts'  => $userCnts,
            'recentCases' => array_slice($recent['data'], 0, 8),
            'totalUsers'  => array_sum($userCnts),
            'residentCnt' => $resStats['total'],
            'purokCnt'    => count(BARANGAY_PUROKS),
            'smsSent'     => $this->notifModel->totalSent(),
        ]);
    }

    // ── User Management ──────────────────────────────────────────────────────

    /** GET /admin/users */
    public function users(): void {
        $role    = $_GET['role'] ?? null;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $users   = $this->userModel->getAll($role, $page);
        $barangays = $this->barangayModel->getList();

        $this->render('admin/users', [
            'users'     => $users,
            'barangays' => $barangays,
            'roleFilter'=> $role,
            'error'     => getFlash('error'),
            'success'   => getFlash('success'),
        ]);
    }

    /** POST /admin/users/create */
    public function createUser(): void {
        requireRole('admin');
        if (!validateCsrf()) { flash('error','Invalid request.'); redirect('/admin/users'); }

        $data = [
            'full_name'   => sanitize($_POST['full_name'] ?? ''),
            'email'       => sanitize($_POST['email'] ?? ''),
            'phone'       => sanitize($_POST['phone'] ?? ''),
            'password'    => $_POST['password'] ?? 'SafeHer@2024',
            'role'        => $_POST['role'] ?? 'complainant',
            'barangay_id' => !empty($_POST['barangay_id']) ? (int)$_POST['barangay_id'] : null,
        ];

        if ($this->userModel->findByEmail($data['email'])) {
            flash('error', 'Email already in use.');
        } else {
            $this->userModel->create($data);
            flash('success', 'User created successfully.');
        }
        redirect('/admin/users');
    }

    /** POST /admin/users/toggle-status */
    public function toggleUserStatus(): void {
        $id = (int)($_POST['id'] ?? 0);
        $this->userModel->toggleStatus($id);
        flash('success', 'User status updated.');
        redirect('/admin/users');
    }

    /** POST /admin/users/delete */
    public function deleteUser(): void {
        $id = (int)($_POST['id'] ?? 0);
        $this->userModel->delete($id);
        flash('success', 'User deleted.');
        redirect('/admin/users');
    }

    // ── Barangay Management ───────────────────────────────────────────────────

    /** GET /admin/barangays */
    public function barangays(): void {
        $page      = max(1,(int)($_GET['page'] ?? 1));
        $barangays = $this->barangayModel->getAll($page);
        $staffList = $this->userModel->getAll('barangay_staff', 1)['data'];

        $this->render('admin/barangays', [
            'barangays' => $barangays,
            'staffList' => $staffList,
            'error'     => getFlash('error'),
            'success'   => getFlash('success'),
        ]);
    }

    /** POST /admin/barangays/create */
    public function createBarangay(): void {
        if (!validateCsrf()) { flash('error','Invalid request.'); redirect('/admin/barangays'); }
        $this->barangayModel->create([
            'name'               => sanitize($_POST['name'] ?? ''),
            'municipality'       => sanitize($_POST['municipality'] ?? ''),
            'province'           => sanitize($_POST['province'] ?? ''),
            'assigned_staff_id'  => !empty($_POST['assigned_staff_id']) ? (int)$_POST['assigned_staff_id'] : null,
        ]);
        flash('success', 'Barangay added successfully.');
        redirect('/admin/barangays');
    }

    /** POST /admin/barangays/update */
    public function updateBarangay(): void {
        $id = (int)($_POST['id'] ?? 0);
        $this->barangayModel->update($id, [
            'name'               => sanitize($_POST['name'] ?? ''),
            'municipality'       => sanitize($_POST['municipality'] ?? ''),
            'province'           => sanitize($_POST['province'] ?? ''),
            'assigned_staff_id'  => !empty($_POST['assigned_staff_id']) ? (int)$_POST['assigned_staff_id'] : null,
        ]);
        flash('success', 'Barangay updated.');
        redirect('/admin/barangays');
    }

    /** POST /admin/barangays/delete */
    public function deleteBarangay(): void {
        $id = (int)($_POST['id'] ?? 0);
        $this->barangayModel->delete($id);
        flash('success', 'Barangay deleted.');
        redirect('/admin/barangays');
    }

    // ── Notifications ─────────────────────────────────────────────────────────

    /** GET /admin/notifications */
    public function notifications(): void {
        $page  = max(1,(int)($_GET['page'] ?? 1));
        $logs  = $this->notifModel->getAll($page);
        $this->render('admin/notifications', [
            'logs'    => $logs,
            'error'   => getFlash('error'),
            'success' => getFlash('success'),
        ]);
    }

    /** POST /admin/notifications/send-blast */
    public function sendBlast(): void {
        if (!validateCsrf()) { flash('error','Invalid request.'); redirect('/admin/notifications'); }

        $target  = $_POST['target'] ?? 'all';
        $message = sanitize($_POST['message'] ?? '');

        if (empty($message)) {
            flash('error', 'Message cannot be empty.');
            redirect('/admin/notifications');
        }

        $role    = $target === 'all' ? null : $target;
        $phones  = $this->userModel->getAllPhones($role);
        $results = sendBulkSMS($phones, $message);
        $sent    = count(array_filter($results, fn($r) => ($r['http_code'] ?? 0) === 200));

        flash('success', "SMS blast sent to $sent/" . count($phones) . " recipients.");
        redirect('/admin/notifications');
    }

    /** Render helper */
    private function render(string $view, array $data = []): void {
        extract($data);
        include BASE_PATH . "/views/$view.php";
    }
}
