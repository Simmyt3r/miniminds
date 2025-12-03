<?php
/**
 * MiniMinds Academy - Parent Registration Page
 * Registration form for new parent accounts
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isParent()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Parent Registration - MiniMinds Academy';
$error_message = '';
$success_message = '';
$form_data = [
    'username' => '',
    'email' => '',
    'full_name' => '',
    'phone' => ''
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once '../includes/auth.php';
    $result = handleParentRegistration();
    
    if ($result['success']) {
        $success_message = $result['message'];
        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_type'] = 'success';
        header('Location: login.php');
        exit();
    } else {
        $error_message = $result['message'];
        $form_data = array_map('sanitizeInput', $_POST);
    }
}

include '../includes/header.php';
?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card registration-card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Logo and Header -->
                    <div class="text-center mb-4">
                        <div class="registration-logo mb-3">
                            <i class="fas fa-user-plus fa-3x text-primary"></i>
                        </div>
                        <h2 class="fw-bold mb-2">Create Parent Account</h2>
                        <p class="text-muted">Join MiniMinds Academy and start your child's learning adventure</p>
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
                    
                    <!-- Registration Form -->
                    <form method="POST" action="../includes/auth.php" class="registration-form" id="registerForm">
                        <input type="hidden" name="action" value="parent_register">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="fas fa-user me-2"></i>Username *
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="username" name="username" 
                                       value="<?php echo htmlspecialchars($form_data['username']); ?>"
                                       placeholder="Choose a username" required>
                                <small class="form-text text-muted">3-20 characters, letters and numbers only</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label fw-semibold">
                                    <i class="fas fa-id-card me-2"></i>Full Name *
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($form_data['full_name']); ?>"
                                       placeholder="Your full name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2"></i>Email Address *
                            </label>
                            <input type="email" class="form-control form-control-lg" 
                                   id="email" name="email" 
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>"
                                   placeholder="your.email@example.com" required>
                            <small class="form-text text-muted">We'll use this for account recovery and notifications</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">
                                <i class="fas fa-phone me-2"></i>Phone Number
                            </label>
                            <input type="tel" class="form-control form-control-lg" 
                                   id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($form_data['phone']); ?>"
                                   placeholder="+234 800 000 0000">
                            <small class="form-text text-muted">Optional - for account verification</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2"></i>Password *
                                </label>
                                <input type="password" class="form-control form-control-lg" 
                                       id="password" name="password" 
                                       placeholder="Create a strong password" required>
                                <small class="form-text text-muted">Minimum 8 characters</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2"></i>Confirm Password *
                                </label>
                                <input type="password" class="form-control form-control-lg" 
                                       id="confirm_password" name="confirm_password" 
                                       placeholder="Re-enter your password" required>
                            </div>
                        </div>
                        
                        <!-- Terms and Privacy -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                <label class="form-check-label" for="agree_terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and 
                                    <a href="#" class="text-decoration-none">Privacy Policy</a> *
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agree_age" name="agree_age" required>
                                <label class="form-check-label" for="agree_age">
                                    I confirm I am at least 18 years old and the legal guardian of the children using this service *
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Send me learning tips and updates (optional)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
                                <i class="fas fa-user-plus me-2"></i>
                                <span id="registerBtnText">Create Account</span>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Security Info -->
                    <div class="mt-4 text-center">
                        <div class="security-info">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <small class="text-muted">
                                Your information is secure and encrypted • 
                                COPPA compliant platform
                            </small>
                        </div>
                    </div>
                    
                    <!-- Divider -->
                    <div class="divider my-4">
                        <div class="divider-line"></div>
                        <span class="divider-text">ALREADY HAVE AN ACCOUNT?</span>
                        <div class="divider-line"></div>
                    </div>
                    
                    <!-- Login Link -->
                    <div class="text-center">
                        <p class="mb-0">
                            Ready to sign in? 
                            <a href="login.php" class="text-decoration-none fw-semibold">
                                Login to your account
                                <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </p>
                    </div>
                </div>
                
                <!-- Features Preview -->
                <div class="card-footer bg-light border-0">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="feature-icon-small">
                                <i class="fas fa-child fa-2x text-primary"></i>
                            </div>
                            <small>Multiple Kids</small>
                        </div>
                        <div class="col-3">
                            <div class="feature-icon-small">
                                <i class="fas fa-chart-line fa-2x text-success"></i>
                            </div>
                            <small>Progress Tracking</small>
                        </div>
                        <div class="col-3">
                            <div class="feature-icon-small">
                                <i class="fas fa-shield-alt fa-2x text-warning"></i>
                            </div>
                            <small>Safe & Secure</small>
                        </div>
                        <div class="col-3">
                            <div class="feature-icon-small">
                                <i class="fas fa-graduation-cap fa-2x text-info"></i>
                            </div>
                            <small>Educational</small>
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

<script>
// Password validation
function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialchar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    return {
        isValid: password.length >= minLength && (hasUpperCase || hasLowerCase) && hasNumbers,
        score: [password.length >= minLength, hasUpperCase, hasLowerCase, hasNumbers, hasSpecialchar].filter(Boolean).length
    };
}

// Real-time password validation
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const validation = validatePassword(password);
    const confirmField = document.getElementById('confirm_password');
    
    // Update password strength indicator
    let strengthText = '';
    let strengthClass = '';
    
    if (password.length === 0) {
        strengthText = '';
        strengthClass = '';
    } else if (validation.score <= 2) {
        strengthText = 'Weak password';
        strengthClass = 'text-danger';
    } else if (validation.score <= 3) {
        strengthText = 'Fair password';
        strengthClass = 'text-warning';
    } else if (validation.score <= 4) {
        strengthText = 'Good password';
        strengthClass = 'text-info';
    } else {
        strengthText = 'Strong password';
        strengthClass = 'text-success';
    }
    
    // Show/hide strength indicator
    let strengthIndicator = document.getElementById('passwordStrength');
    if (!strengthIndicator) {
        strengthIndicator = document.createElement('small');
        strengthIndicator.id = 'passwordStrength';
        this.parentNode.appendChild(strengthIndicator);
    }
    
    strengthIndicator.textContent = strengthText;
    strengthIndicator.className = strengthClass;
    
    // Re-validate confirmation if it has value
    if (confirmField.value) {
        validatePasswordMatch();
    }
});

// Password match validation
function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmFeedback = document.getElementById('confirmFeedback');
    
    if (!confirmFeedback) {
        confirmFeedback = document.createElement('small');
        confirmFeedback.id = 'confirmFeedback';
        document.getElementById('confirm_password').parentNode.appendChild(confirmFeedback);
    }
    
    if (confirmPassword.length === 0) {
        confirmFeedback.textContent = '';
        confirmFeedback.className = '';
        document.getElementById('confirm_password').classList.remove('is-valid', 'is-invalid');
    } else if (password === confirmPassword) {
        confirmFeedback.textContent = 'Passwords match ✓';
        confirmFeedback.className = 'text-success';
        document.getElementById('confirm_password').classList.remove('is-invalid');
        document.getElementById('confirm_password').classList.add('is-valid');
    } else {
        confirmFeedback.textContent = 'Passwords do not match ✗';
        confirmFeedback.className = 'text-danger';
        document.getElementById('confirm_password').classList.remove('is-valid');
        document.getElementById('confirm_password').classList.add('is-invalid');
    }
}

document.getElementById('confirm_password').addEventListener('input', validatePasswordMatch);

// Username validation
document.getElementById('username').addEventListener('input', function() {
    const username = this.value;
    const feedback = document.getElementById('usernameFeedback');
    
    if (!feedback) {
        feedback = document.createElement('small');
        feedback.id = 'usernameFeedback';
        this.parentNode.appendChild(feedback);
    }
    
    if (username.length === 0) {
        feedback.textContent = '';
        feedback.className = '';
        this.classList.remove('is-valid', 'is-invalid');
    } else if (username.length < 3) {
        feedback.textContent = 'Username must be at least 3 characters';
        feedback.className = 'text-danger';
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else if (!/^[a-zA-Z0-9]+$/.test(username)) {
        feedback.textContent = 'Username can only contain letters and numbers';
        feedback.className = 'text-danger';
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else {
        feedback.textContent = 'Username available';
        feedback.className = 'text-success';
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    }
});

// Form validation and submission
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const agreeTerms = document.getElementById('agree_terms').checked;
    const agreeAge = document.getElementById('agree_age').checked;
    
    // Validate passwords match
    if (password !== confirmPassword) {
        e.preventDefault();
        MiniMinds.showNotification('Passwords do not match', 'danger');
        return;
    }
    
    // Validate password strength
    const validation = validatePassword(password);
    if (!validation.isValid) {
        e.preventDefault();
        MiniMinds.showNotification('Please choose a stronger password (at least 8 characters)', 'danger');
        return;
    }
    
    // Validate terms agreement
    if (!agreeTerms || !agreeAge) {
        e.preventDefault();
        MiniMinds.showNotification('Please accept the terms and confirm you are 18+ years old', 'danger');
        return;
    }
    
    // Show loading state
    const registerBtn = document.getElementById('registerBtn');
    const registerBtnText = document.getElementById('registerBtnText');
    
    registerBtn.disabled = true;
    registerBtnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
});

// Check username availability (would normally be an API call)
document.getElementById('username').addEventListener('blur', function() {
    const username = this.value.trim();
    
    if (username.length >= 3 && /^[a-zA-Z0-9]+$/.test(username)) {
        // Simulate API check
        setTimeout(() => {
            // For demo, allow all usernames except taken ones
            const takenUsernames = ['admin', 'testparent'];
            if (takenUsernames.includes(username.toLowerCase())) {
                const feedback = document.getElementById('usernameFeedback');
                if (feedback) {
                    feedback.textContent = 'Username already taken';
                    feedback.className = 'text-danger';
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            }
        }, 500);
    }
});
</script>

<style>
.registration-card {
    border-radius: 20px;
    overflow: hidden;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.registration-logo {
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

.form-control-lg.is-valid {
    border-color: #28a745;
    background-image: none;
}

.form-control-lg.is-invalid {
    border-color: #dc3545;
    background-image: none;
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
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.feature-icon-small {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    transition: transform 0.3s ease;
}

.feature-icon-small:hover {
    transform: translateY(-3px);
}

.security-info {
    background: rgba(72, 187, 120, 0.1);
    border-radius: 10px;
    padding: 0.75rem;
}

@media (max-width: 768px) {
    .registration-card {
        margin: 1rem;
        border-radius: 15px;
    }
    
    .card-body {
        padding: 2rem !important;
    }
    
    .registration-logo {
        width: 60px;
        height: 60px;
    }
    
    .registration-logo i {
        font-size: 2rem !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>