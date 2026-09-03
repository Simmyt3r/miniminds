<?php
/**
 * MiniMinds Academy - Parent Login Page
 * Secure login interface for parents
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isParent()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Parent Login - MiniMinds Academy';
$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once '../includes/auth.php';
    $result = handleParentLogin();
    
    if ($result['success']) {
        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . $result['redirect']);
        exit();
    } else {
        $error_message = $result['message'];
    }
}

include '../includes/header.php';
?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card login-card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Logo and Header -->
                    <div class="text-center mb-4">
                        <div class="login-logo mb-3">
                            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                        </div>
                        <h2 class="fw-bold mb-2">Parent Login</h2>
                        <p class="text-muted">Welcome back to MiniMinds Academy</p>
                    </div>
                    
                    <!-- Error/Success Messages -->
                    <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" action="../includes/auth.php" class="login-form" id="loginForm">
                        <input type="hidden" name="action" value="parent_login">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-4">
                            <label for="login_identifier" class="form-label fw-semibold">
                                <i class="fas fa-user me-2"></i>Username or Email
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control form-control-lg" 
                                       id="login_identifier" name="login_identifier" 
                                       placeholder="Enter your username or email" 
                                       required autofocus>
                            </div>
                            <small class="form-text text-muted">Enter the username or email you used to register</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" class="form-control form-control-lg" 
                                       id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Remember me for 30 days
                                </label>
                            </div>
                            <a href="#" class="text-decoration-none" onclick="showForgotPassword()">
                                <small>Forgot password?</small>
                            </a>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                <span id="loginBtnText">Login to Dashboard</span>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Login Security Info -->
                    <div class="mt-4 text-center">
                        <div class="security-info">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <small class="text-muted">
                                Secure login with 256-bit encryption • 
                                <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Divider -->
                    <div class="divider my-4">
                        <div class="divider-line"></div>
                        <span class="divider-text">OR</span>
                        <div class="divider-line"></div>
                    </div>
                    
                    <!-- Register Link -->
                    <div class="text-center">
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="register.php" class="text-decoration-none fw-semibold">
                                Create free account
                                <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </p>
                    </div>
                    
                    <!-- Quick Demo Access -->
                    <div class="mt-4">
                        <div class="card bg-light border-0">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-info me-3"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted">
                                            <strong>New to MiniMinds?</strong> Try our demo account:
                                            Username: <code>demo</code> • Password: <code>demo123</code>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Login Features -->
                <div class="card-footer bg-light border-0">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="login-feature">
                                <i class="fas fa-child fa-2x text-primary mb-2"></i>
                                <small>Multiple Kids</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="login-feature">
                                <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                <small>Progress Tracking</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="login-feature">
                                <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                                <small>Safe & Secure</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Back to Home -->
            <div class="text-center mt-4">
                <a href="../index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../includes/auth.php" id="forgotPasswordForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="forgot_password">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <p class="text-muted mb-3">
                        Enter your email address and we'll send you instructions to reset your password.
                    </p>
                    
                    <div class="mb-3">
                        <label for="reset_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="reset_email" name="email" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        For demo purposes, passwords reset to <code>parent123</code>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password visibility toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
});

// Show forgot password modal
function showForgotPassword() {
    const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
    modal.show();
}

// Handle forgot password form
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    
    fetch('../includes/auth.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
            modal.hide();
            
            // Show notification
            MiniMinds.showNotification(data.message, 'success');
            
            // Reset form
            this.reset();
        } else {
            // Show error in modal
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${data.message}`;
            
            const modalBody = document.querySelector('#forgotPasswordModal .modal-body');
            modalBody.insertBefore(errorDiv, modalBody.firstChild);
            
            setTimeout(() => errorDiv.remove(), 5000);
        }
    })
    .catch(error => {
        console.error('Forgot password error:', error);
        MiniMinds.showNotification('Network error. Please try again.', 'danger');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Login form handling with loading state
document.getElementById('loginForm').addEventListener('submit', function() {
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');
    
    loginBtn.disabled = true;
    loginBtnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging in...';
});

// Auto-fill demo credentials helper
document.addEventListener('keydown', function(e) {
    // Ctrl+Shift+D to fill demo credentials
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        document.getElementById('login_identifier').value = 'testparent';
        document.getElementById('password').value = 'parent123';
        
        // Show notification
        MiniMinds.showNotification('Demo credentials filled! Click Login to continue.', 'info');
    }
});

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const identifier = document.getElementById('login_identifier').value.trim();
    const password = document.getElementById('password').value;
    
    if (identifier.length < 3) {
        e.preventDefault();
        MiniMinds.showNotification('Please enter a valid username or email', 'danger');
        return;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        MiniMinds.showNotification('Password must be at least 6 characters', 'danger');
        return;
    }
});

// Add enter key support for form submission
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const activeElement = document.activeElement;
        if (activeElement.tagName === 'INPUT') {
            const form = activeElement.closest('form');
            if (form) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    }
});
</script>

<style>
.login-card {
    border-radius: 20px;
    overflow: hidden;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.login-logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.form-control-lg {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control-lg:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group-text {
    border-radius: 12px 0 0 12px;
    border: 2px solid #e9ecef;
    background: #f8f9fa;
    color: var(--primary-color);
}

.btn-lg {
    border-radius: 12px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    position: relative;
}

.divider-line {
    flex: 1;
    height: 1px;
    background: #e9ecef;
}

.divider-text {
    padding: 0 1rem;
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.login-feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    transition: transform 0.3s ease;
}

.login-feature:hover {
    transform: translateY(-3px);
}

.security-info {
    background: rgba(72, 187, 120, 0.1);
    border-radius: 10px;
    padding: 0.75rem;
}

@media (max-width: 768px) {
    .login-card {
        margin: 1rem;
        border-radius: 15px;
    }
    
    .card-body {
        padding: 2rem !important;
    }
    
    .login-logo {
        width: 60px;
        height: 60px;
    }
    
    .login-logo i {
        font-size: 2rem !important;
    }
}

/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spinner {
    animation: spin 1s linear infinite;
}
</style>

<?php include '../includes/footer.php'; ?>