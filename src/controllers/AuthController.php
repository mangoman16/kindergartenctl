<?php
/**
 * Authentication Controller
 */

class AuthController extends Controller
{
    public function __construct()
    {
        $this->setLayout('auth');
    }

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if (Auth::check()) {
            $this->redirect('/');
            return;
        }

        $this->setTitle(__('auth.login'));
        $this->render('auth/login');
    }

    /**
     * Handle login attempt
     */
    public function login(): void
    {
        // Check IP ban
        $ip = getClientIp();
        $banStatus = isIpBanned($ip);

        if ($banStatus === 'permanent') {
            Session::setFlash('error', __('auth.ip_banned_permanent'));
            $this->redirect('/login');
            return;
        }

        if ($banStatus === 'temporary') {
            Session::setFlash('error', __('auth.ip_banned'));
            $this->redirect('/login');
            return;
        }

        $login = $this->getPost('login');
        $password = $this->getPost('password');
        $remember = (bool)$this->getPost('remember');

        // Validate
        $validator = Validator::make(
            ['login' => $login, 'password' => $password],
            ['login' => 'required', 'password' => 'required']
        );

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput(['login' => $login]);
            $this->redirect('/login');
            return;
        }

        // Attempt login
        if (Auth::attempt($login, $password, $remember)) {
            // Reset failed attempts on successful login
            resetFailedAttempts($ip);

            Session::setFlash('success', __('auth.welcome_back'));
            $this->redirect('/');
            return;
        }

        // Record failed attempt
        recordFailedAttempt($ip, 'Invalid login credentials');

        Session::setFlash('error', __('auth.login_failed'));
        Session::setOldInput(['login' => $login]);
        $this->redirect('/login');
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        Auth::logout();
        Session::setFlash('success', 'Sie wurden abgemeldet.');
        $this->redirect('/login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        $this->setTitle(__('auth.forgot_password'));
        $this->render('auth/forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(): void
    {
        $email = $this->getPost('email');

        // Validate
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setOldInput(['email' => $email]);
            $this->redirect('/forgot-password');
            return;
        }

        // Find user by email
        require_once SRC_PATH . '/models/User.php';
        $user = User::findByEmail($email);

        if ($user) {
            // Generate token
            $token = Auth::generatePasswordResetToken($user['id']);
            $resetLink = App::baseUrl() . '/reset-password?token=' . $token;

            // Try to send email
            require_once SRC_PATH . '/services/Mailer.php';
            $mailer = new Mailer();

            if ($mailer->isConfigured()) {
                $sent = $mailer->sendPasswordReset($email, $resetLink);
                if (!$sent) {
                    // Log error but don't expose to user
                    logMessage("Failed to send password reset email to {$email}: " . implode(', ', $mailer->getErrors()), 'error');
                }
            } else {
                // Log the reset link for development when SMTP is not configured
                logMessage("Password reset link for {$email}: {$resetLink}", 'info');
            }
        }

        // Always show success message (prevent email enumeration)
        Session::setFlash('success', __('auth.reset_link_sent'));
        $this->redirect('/login');
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(): void
    {
        $token = $this->getQuery('token');

        if (!$token) {
            Session::setFlash('error', __('auth.reset_token_invalid'));
            $this->redirect('/login');
            return;
        }

        // Validate token
        $userId = Auth::validatePasswordResetToken($token);

        if (!$userId) {
            Session::setFlash('error', __('auth.reset_token_invalid'));
            $this->redirect('/login');
            return;
        }

        $this->setTitle(__('auth.reset_password'));
        $this->render('auth/reset-password', ['token' => $token]);
    }

    /**
     * Reset password
     */
    public function resetPassword(): void
    {
        $token = $this->getPost('token');
        $password = $this->getPost('password');
        $passwordConfirmation = $this->getPost('password_confirmation');

        // Validate token
        $userId = Auth::validatePasswordResetToken($token);

        if (!$userId) {
            Session::setFlash('error', __('auth.reset_token_invalid'));
            $this->redirect('/login');
            return;
        }

        // Validate password
        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => $passwordConfirmation],
            ['password' => 'required|min:8|confirmed']
        );

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            $this->redirect('/reset-password?token=' . $token);
            return;
        }

        // Update password
        require_once SRC_PATH . '/models/User.php';
        User::updatePassword($userId, $password);

        // Mark token as used
        Auth::markPasswordResetUsed($token);

        Session::setFlash('success', __('auth.password_reset_success'));
        $this->redirect('/login');
    }
}
