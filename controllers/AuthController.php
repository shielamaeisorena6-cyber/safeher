<?php
// ─── Auth Controller ──────────────────────────────────────────────────────────
class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /** GET / — Landing page */
    public function landing(): void {
        $caseModel = new CaseModel();
        $stats = $caseModel->countByStatus();
        $barangayModel = new Barangay();
        $data = [
            'totalCases'    => $stats['total'],
            'resolved'      => $stats['resolved'],
            'pending'       => $stats['pending'],
            'barangays'     => $barangayModel->count(),
        ];
        $this->render('public/landing', $data);
    }

    /** GET /login  POST /login */
    public function login(): void {
        if (isLoggedIn()) {
            redirect(dashboardUrl());
        }

        $error = getFlash('error');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validateCsrf()) {
                flash('error', 'Invalid request. Please try again.');
                redirect('/login');
            }

            $email    = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->authenticate($email, $password);

            if ($user) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['phone']     = $user['phone'];
                if ($user['barangay_id']) {
                    $_SESSION['barangay_id'] = $user['barangay_id'];
                }
                redirect(dashboardUrl());
            } else {
                flash('error', 'Invalid email or password. Please try again.');
                redirect('/login');
            }
        }

        $this->render('public/login', ['error' => $error]);
    }

    /** GET /register  POST /register */
    public function register(): void {
        if (isLoggedIn()) {
            redirect(dashboardUrl());
        }

        $error   = getFlash('error');
        $success = getFlash('success');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validateCsrf()) {
                flash('error', 'Invalid request. Please try again.');
                redirect('/register');
            }

            $fullName  = sanitize($_POST['full_name'] ?? '');
            $email     = sanitize($_POST['email'] ?? '');
            $phone     = sanitize($_POST['phone'] ?? '');
            $password  = $_POST['password'] ?? '';
            $confirm   = $_POST['confirm_password'] ?? '';

            // Validation
            if (!$fullName || !$email || !$phone || !$password) {
                flash('error', 'All fields are required.');
                redirect('/register');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Please enter a valid email address.');
                redirect('/register');
            }
            if (strlen($password) < 8) {
                flash('error', 'Password must be at least 8 characters long.');
                redirect('/register');
            }
            if ($password !== $confirm) {
                flash('error', 'Passwords do not match.');
                redirect('/register');
            }
            if ($this->userModel->findByEmail($email)) {
                flash('error', 'An account with this email already exists.');
                redirect('/register');
            }

            $userId = $this->userModel->create([
                'full_name'   => $fullName,
                'email'       => $email,
                'phone'       => $phone,
                'password'    => $password,
                'role'        => 'complainant',
                'barangay_id' => null,
            ]);

            if ($userId) {
                // Send welcome SMS
                sendSMS($phone, "Welcome to SafeHer, $fullName! Your account has been created. You can now file reports safely and track their progress.");
                flash('success', 'Account created successfully! You can now login.');
                redirect('/login');
            } else {
                flash('error', 'Registration failed. Please try again.');
                redirect('/register');
            }
        }

        $this->render('public/register', ['error' => $error, 'success' => $success]);
    }

    /** GET /logout */
    public function logout(): void {
        session_destroy();
        redirect('/login');
    }

    /** Render a view */
    private function render(string $view, array $data = []): void {
        extract($data);
        include BASE_PATH . "/views/$view.php";
    }
}
