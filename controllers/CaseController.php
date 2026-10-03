<?php
// ─── Case Controller ──────────────────────────────────────────────────────────
class CaseController {
    private CaseModel $caseModel;
    private Barangay  $barangayModel;
    private User      $userModel;
    private Notification $notifModel;

    public function __construct() {
        requireLogin();
        $this->caseModel     = new CaseModel();
        $this->barangayModel = new Barangay();
        $this->userModel     = new User();
        $this->notifModel    = new Notification();
    }

    /** GET /admin/cases */
    public function adminList(): void {
        requireRole('admin');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'           => $_GET['status'] ?? null,
            'type_of_violence' => $_GET['type'] ?? null,
            'barangay_id'      => !empty($_GET['barangay_id']) ? (int)$_GET['barangay_id'] : null,
            'search'           => $_GET['search'] ?? null,
        ];
        $cases     = $this->caseModel->getAll(array_filter($filters), $page);
        $barangays = $this->barangayModel->getList();

        $this->render('admin/cases', [
            'cases'     => $cases,
            'barangays' => $barangays,
            'filters'   => $filters,
            'error'     => getFlash('error'),
            'success'   => getFlash('success'),
        ]);
    }

    /** GET /complainant/report  POST /complainant/report */
    public function submitReport(): void {
        requireRole('complainant');
        $barangays = $this->barangayModel->getList();
        $error     = getFlash('error');
        $success   = getFlash('success');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validateCsrf()) { flash('error','Invalid request.'); redirect('/complainant/report'); }

            $barangayId = (int)($_POST['barangay_id'] ?? 1) ?: 1;
            $purok      = trim($_POST['purok'] ?? '');
            $locDetail  = trim($_POST['location'] ?? '');
            $location   = $purok ? ($purok . ($locDetail ? ' (' . $locDetail . ')' : '')) : $locDetail;

            $evidence   = null;

            // Handle file upload
            if (!empty($_FILES['evidence']['name'])) {
                $evidence = uploadEvidence($_FILES['evidence']);
                if ($evidence === false) {
                    flash('error', 'Invalid file. Allowed: JPG, PNG, PDF, MP4 (max ' . MAX_UPLOAD_MB . 'MB).');
                    redirect('/complainant/report');
                }
            }

            // Get assigned staff for this barangay
            $barangay    = $this->barangayModel->findById($barangayId);
            $staffId     = $barangay['assigned_staff_id'] ?? null;

            $caseId = $this->caseModel->create([
                'complainant_id'    => (int)$_SESSION['user_id'],
                'barangay_id'       => $barangayId,
                'type_of_violence'  => $_POST['type_of_violence'] ?? 'others',
                'incident_date'     => $_POST['incident_date'] ?? date('Y-m-d'),
                'location'          => sanitize($location),
                'description'       => sanitize($_POST['description'] ?? ''),
                'evidence_path'     => $evidence,
                'assigned_staff_id' => $staffId,
            ]);

            if ($caseId) {
                $case = $this->caseModel->findById($caseId);

                // Notify complainant
                sendSMS(
                    $_SESSION['phone'],
                    "SafeHer: Your report (Case #{$case['case_number']}) has been successfully filed and is under review. We will notify you of any updates."
                );

                // Notify assigned barangay staff
                if ($staffId) {
                    $staff = $this->userModel->findById($staffId);
                    if ($staff) {
                        sendSMS(
                            $staff['phone'],
                            "SafeHer: A new case ({$case['case_number']}) has been filed in your barangay. Please log in to review."
                        );
                    }
                }

                flash('success', "Report filed successfully! Your case number is: <strong>{$case['case_number']}</strong>");
                redirect('/complainant/my-cases');
            } else {
                flash('error', 'Failed to submit your report. Please try again.');
                redirect('/complainant/report');
            }
        }

        $this->render('complainant/report', [
            'barangays' => $barangays,
            'error'     => $error,
            'success'   => $success,
        ]);
    }

    /** POST /barangay/update-case */
    public function updateCaseStatus(): void {
        requireRole('barangay_staff', 'admin');
        if (!validateCsrf()) { flash('error', 'Invalid request.'); redirect(dashboardUrl()); }

        $caseId  = (int)($_POST['case_id'] ?? 0);
        $status  = $_POST['status'] ?? '';
        $remarks = sanitize($_POST['remarks'] ?? '');

        $case = $this->caseModel->findById($caseId);
        if (!$case) { flash('error', 'Case not found.'); redirect(dashboardUrl()); }

        $this->caseModel->updateStatus($caseId, $status, $remarks, (int)$_SESSION['user_id']);

        // Notify complainant
        $complainant = $this->userModel->findById((int)$case['complainant_id']);
        if ($complainant) {
            $label = statusLabel($status);
            sendSMS(
                $complainant['phone'],
                "SafeHer Update: Case #{$case['case_number']} status has been changed to \"$label\". Remarks: $remarks"
            );
        }

        flash('success', 'Case status updated and complainant notified via SMS.');
        $redirect = userRole() === 'admin' ? '/admin/cases' : '/barangay/cases';
        redirect($redirect);
    }

    /** POST /admin/cases/delete */
    public function deleteCase(): void {
        requireRole('admin');
        $id = (int)($_POST['id'] ?? 0);
        $this->caseModel->delete($id);
        flash('success', 'Case deleted.');
        redirect('/admin/cases');
    }

    /** GET /admin/cases/view?id=X  or  GET /complainant/case-view?id=X */
    public function viewCase(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $case = $this->caseModel->findById($id);

        if (!$case) { flash('error', 'Case not found.'); redirect(dashboardUrl()); }

        // Access control
        if (userRole() === 'complainant' && $case['complainant_id'] != $_SESSION['user_id']) {
            redirect('/complainant/dashboard');
        }

        $updates   = $this->caseModel->getUpdates($id);
        $barangays = $this->barangayModel->getList();

        $view = match(userRole()) {
            'admin'          => 'admin/case_view',
            'barangay_staff' => 'barangay/case_view',
            default          => 'complainant/case_view',
        };

        $this->render($view, [
            'case'    => $case,
            'updates' => $updates,
            'barangays' => $barangays,
            'success' => getFlash('success'),
            'error'   => getFlash('error'),
        ]);
    }

    private function render(string $view, array $data = []): void {
        extract($data);
        include BASE_PATH . "/views/$view.php";
    }
}
