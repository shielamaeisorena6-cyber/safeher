<?php
// ─── Complainant Controller ───────────────────────────────────────────────────
class ComplainantController {
    private CaseModel $caseModel;
    private Notification $notifModel;

    public function __construct() {
        requireRole('complainant');
        $this->caseModel  = new CaseModel();
        $this->notifModel = new Notification();
    }

    /** GET /complainant/dashboard */
    public function dashboard(): void {
        $userId = (int)$_SESSION['user_id'];
        $cases  = $this->caseModel->getForComplainant($userId, 1);
        $stats  = $this->caseModel->countByStatus();

        // Per-user stats
        $allCases   = $cases['data'];
        $myCounts   = ['total'=>$cases['total'], 'pending'=>0, 'under_investigation'=>0, 'resolved'=>0, 'dismissed'=>0];
        foreach ($allCases as $c) $myCounts[$c['status']] = ($myCounts[$c['status']] ?? 0) + 1;

        $recentNotifs = $this->notifModel->getForUser($userId, 5);

        $this->render('complainant/dashboard', [
            'cases'        => $cases,
            'myCounts'     => $myCounts,
            'recentNotifs' => $recentNotifs,
            'success'      => getFlash('success'),
            'error'        => getFlash('error'),
        ]);
    }

    /** GET /complainant/my-cases */
    public function myCases(): void {
        $userId = (int)$_SESSION['user_id'];
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $cases  = $this->caseModel->getForComplainant($userId, $page);

        $this->render('complainant/my_cases', [
            'cases'   => $cases,
            'success' => getFlash('success'),
            'error'   => getFlash('error'),
        ]);
    }

    private function render(string $view, array $data = []): void {
        extract($data);
        include BASE_PATH . "/views/$view.php";
    }
}
