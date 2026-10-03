<?php
// ─── Analytics Controller ─────────────────────────────────────────────────────
class AnalyticsController {
    private CaseModel $caseModel;
    private Barangay  $barangayModel;

    public function __construct() {
        requireRole('admin');
        $this->caseModel     = new CaseModel();
        $this->barangayModel = new Barangay();
    }

    /** GET /admin/analytics */
    public function index(): void {
        $monthlyTrend   = $this->caseModel->monthlyTrend(12);
        $byViolenceType = $this->caseModel->countByViolenceType();
        $byBarangay     = $this->caseModel->countByBarangay();
        $byPurok        = $this->caseModel->countByPurok();
        $statusSummary  = $this->caseModel->countByStatus();

        // Compute resolution rate
        $total      = $statusSummary['total'];
        $resolvedRate = $total > 0 ? round(($statusSummary['resolved'] / $total) * 100, 1) : 0;

        // Prepare Chart.js data
        $trendLabels = array_column($monthlyTrend, 'month');
        $trendData   = array_column($monthlyTrend, 'total');

        $vtLabels = array_map(fn($r) => violenceLabel($r['type_of_violence']), $byViolenceType);
        $vtData   = array_column($byViolenceType, 'total');

        $brLabels = array_column($byBarangay, 'barangay');
        $brData   = array_column($byBarangay, 'total');

        $purokLabels = array_column($byPurok, 'purok');
        $purokData   = array_column($byPurok, 'total');

        $this->render('admin/analytics', [
            'statusSummary'  => $statusSummary,
            'resolvedRate'   => $resolvedRate,
            'trendLabels'    => json_encode($trendLabels),
            'trendData'      => json_encode($trendData),
            'vtLabels'       => json_encode($vtLabels),
            'vtData'         => json_encode($vtData),
            'brLabels'       => json_encode($brLabels),
            'brData'         => json_encode($brData),
            'purokLabels'    => json_encode($purokLabels),
            'purokData'      => json_encode($purokData),
            'byBarangay'     => $byBarangay,
            'byPurok'        => $byPurok,
            'byViolenceType' => $byViolenceType,
        ]);
    }

    /** GET /admin/analytics/export-csv */
    public function exportCsv(): void {
        requireRole('admin');

        $cases = $this->caseModel->getAll([], 1)['data'];
        $db    = getDB();
        $stmt  = $db->query(
            "SELECT c.case_number, u.full_name AS complainant, b.name AS barangay,
                    c.type_of_violence, c.incident_date, c.status, c.created_at
             FROM cases c
             LEFT JOIN users u ON c.complainant_id = u.id
             LEFT JOIN barangays b ON c.barangay_id = b.id
             ORDER BY c.created_at DESC"
        );
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="safeher_cases_' . date('Ymd') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Case Number','Complainant','Barangay','Type of Violence','Incident Date','Status','Date Filed']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['case_number'], $row['complainant'], $row['barangay'],
                violenceLabel($row['type_of_violence']), $row['incident_date'],
                statusLabel($row['status']), fmtDateTime($row['created_at']),
            ]);
        }
        fclose($out);
        exit;
    }

    private function render(string $view, array $data = []): void {
        extract($data);
        include BASE_PATH . "/views/$view.php";
    }
}
