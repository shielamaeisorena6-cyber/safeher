<?php
// ─── Barangay Staff Controller ────────────────────────────────────────────────
class BarangayController {
    private CaseModel $caseModel;
    private Barangay  $barangayModel;

    public function __construct() {
        requireRole('barangay_staff');
        $this->caseModel     = new CaseModel();
        $this->barangayModel = new Barangay();
    }

    /** GET /barangay/dashboard */
    public function dashboard(): void {
        $barangay = $this->barangayModel->findByStaff((int)$_SESSION['user_id']);
        if (!$barangay) {
            $this->render('barangay/no_barangay');
            return;
        }

        $_SESSION['barangay_id'] = $barangay['id'];
        $stats  = $this->caseModel->countByStatus((int)$barangay['id']);
        $recent = $this->caseModel->getForBarangay((int)$barangay['id'], [], 1);

        $this->render('barangay/dashboard', [
            'barangay'    => $barangay,
            'stats'       => $stats,
            'recentCases' => array_slice($recent['data'], 0, 8),
            'success'     => getFlash('success'),
            'error'       => getFlash('error'),
        ]);
    }

    /** GET /barangay/cases */
    public function cases(): void {
        $barangayId = (int)($_SESSION['barangay_id'] ?? 0);
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $filters    = ['status' => $_GET['status'] ?? null];
        $cases      = $this->caseModel->getForBarangay($barangayId, array_filter($filters), $page);

        $this->render('barangay/cases', [
            'cases'      => $cases,
            'statusFilter'=> $_GET['status'] ?? '',
            'success'    => getFlash('success'),
            'error'      => getFlash('error'),
        ]);
    }

    private function render(string $view, array $data = []): void {
        extract($data);
        include BASE_PATH . "/views/$view.php";
    }
}
