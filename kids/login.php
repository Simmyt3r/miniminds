<?php
/**
 * MiniMinds Academy - Child Login Page
 * Fun, simple login interface for children
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in as child
if (isChild()) {
    header('Location: dashboard.php');
    exit();
}

// Check if parent is logged in (for child selection)
$parent_logged_in = isParent();
$children = [];
$selected_child_id = null;

if ($parent_logged_in) {
    $children = getParentChildren($_SESSION['user_id']);
} else {
    // Redirect to parent login if no parent session
    header('Location: ../parents/login.php?redirect=child');
    exit();
}

$page_title = 'Kids Login - MiniMinds Academy';
$error_message = '';
$success_message = '';

// Handle child login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once '../includes/auth.php';
    $result = handleChildLogin();
    
    if ($result['success']) {
        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . $result['redirect']);
        exit();
    } else {
        $error_message = $result['message'];
    }
}

// Get selected child from session or POST
$selected_child_id = $_SESSION['selected_child_id'] ?? ($_POST['child_id'] ?? null);

include '../includes/header.php';
?>

<div class="kids-login-page">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
            <div class="shape shape-5"></div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-lg-6 col-md-8">
                <!-- Welcome Section -->
                <div class="welcome-section text-center mb-4">
                    <div class="welcome-logo mb-3">
                        <img src="../assets/images/logo-kids.png" alt="MiniMinds Academy" class="img-fluid">
                    </div>
                    <h1 class="welcome-title">
                        <span class="text-bounce">🎮</span>
                        Ready to Play & Learn?
                        <span class="text-bounce">🌟</span>
                    </h1>
                    <p class="welcome-subtitle">
                        Choose your avatar and let the adventure begin!
                    </p>
                </div>

                <!-- Error/Success Messages -->
                <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show kids-alert" role="alert">
                    <div class="alert-content">
                        <span class="alert-emoji">😅</span>
                        <span class="alert-message"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show kids-alert" role="alert">
                    <div class="alert-content">
                        <span class="alert-emoji">🎉</span>
                        <span class="alert-message"><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Child Selection Form -->
                <?php if (!$selected_child_id): ?>
                <div class="kids-card">
                    <div class="kids-card-header">
                        <h2 class="kids-title">
                            <i class="fas fa-users me-2"></i>
                            Who's Playing Today?
                        </h2>
                    </div>
                    
                    <div class="kids-card-body">
                        <form method="POST" action="login.php" id="childSelectionForm">
                            <input type="hidden" name="action" value="select_child">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="children-grid">
                                <?php if ($children): ?>
                                    <?php foreach ($children as $child): ?>
                                    <div class="child-avatar-card" onclick="selectChild(<?php echo $child['id']; ?>)">
                                        <input type="radio" name="child_id" value="<?php echo $child['id']; ?>" id="child_<?php echo $child['id']; ?>" class="d-none">
                                        <label for="child_<?php echo $child['id']; ?>" class="child-avatar-label">
                                            <div class="avatar-container">
                                                <img src="../assets/images/avatars/<?php echo htmlspecialchars($child['avatar_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($child['display_name']; ?>" 
                                                     class="child-avatar">
                                                <div class="avatar-level-badge">
                                                    Level <?php echo $child['current_level']; ?>
                                                </div>
                                            </div>
                                            <h3 class="child-name"><?php echo htmlspecialchars($child['display_name']); ?></h3>
                                            <div class="child-stats">
                                                <span class="stat-badge">
                                                    <i class="fas fa-bolt"></i>
                                                    <?php echo $child['current_xp']; ?> XP
                                                </span>
                                                <span class="stat-badge">
                                                    <i class="fas fa-coins"></i>
                                                    <?php echo number_format($child['total_points']); ?>
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div class="no-children-message">
                                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                                    <h4>No Kids Yet!</h4>
                                    <p class="text-muted">
                                        Ask your parent to add your profile first.
                                    </p>
                                    <a href="../parents/children.php" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Add Child Profile
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- PIN Entry Form -->
                <div class="kids-card">
                    <div class="kids-card-header">
                        <h2 class="kids-title">
                            <span class="welcome-emoji">🔐</span>
                            Enter Your Secret PIN
                        </h2>
                        <p class="kids-subtitle">
                            Hi, <?php echo htmlspecialchars($children[array_search($selected_child_id, array_column($children, 'id'))]['display_name'] ?? 'Friend'); ?>!
                        </p>
                    </div>
                    
                    <div class="kids-card-body">
                        <form method="POST" action="../includes/auth.php" id="pinForm" class="pin-entry-form">
                            <input type="hidden" name="action" value="child_login">
                            <input type="hidden" name="child_id" value="<?php echo $selected_child_id; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="pin-display">
                                <div class="pin-dots">
                                    <div class="pin-dot" id="pin1"></div>
                                    <div class="pin-dot" id="pin2"></div>
                                    <div class="pin-dot" id="pin3"></div>
                                    <div class="pin-dot" id="pin4"></div>
                                </div>
                                <input type="password" name="pin_code" id="pinInput" maxlength="4" class="d-none" required>
                            </div>
                            
                            <div class="pin-keypad">
                                <div class="keypad-row">
                                    <button type="button" class="pin-btn" onclick="addPinDigit('1')">1</button>
                                    <button type="button" class="pin-btn" onclick="addPinDigit('2')">2</button>
                                    <button type="button" class="pin-btn" onclick="addPinDigit('3')">3</button>
                                </div>
                                <div class="keypad-row">
                                    <button type="button" class="pin-btn" onclick="addPinDigit('4')">4</button>
                                    <button type="button" class="pin-btn" onclick="addPinDigit('5')">5</button>
                                    <button type="button" class="pin-btn" onclick="addPinDigit('6')">6</button>
                                </div>
                                <div class="keypad-row">
                                    <button type="button" class="pin-btn" onclick="addPinDigit('7')">7</button>
                                    <button type="button" class="pin-btn" onclick="addPinDigit('8')">8</button>
                                    <button type="button" class="pin-btn" onclick="addPinDigit('9')">9</button>
                                </div>
                                <div class="keypad-row">
                                    <button type="button" class="pin-btn pin-clear" onclick="clearPin()">Clear</button>
                                    <button type="button" class="pin-btn" onclick="addPinDigit('0')">0</button>
                                    <button type="button" class="pin-btn pin-backspace" onclick="removePinDigit()">
                                        <i class="fas fa-backspace"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="pin-actions">
                                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="goBack()">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="loginBtn" disabled>
                                    <i class="fas fa-play me-2"></i>Let's Play!
                                </button>
                            </div>
                        </form>
                        
                        <div class="pin-help">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Ask your parent if you forgot your PIN</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Parent Switch -->
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-light parent-switch-btn" onclick="switchToParent()">
                        <i class="fas fa-user-shield me-2"></i>
                        Parent Area
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPin = '';
const pinLength = 4;

// Child selection
function selectChild(childId) {
    // Store in session for next step
    const form = document.getElementById('childSelectionForm');
    const radioInput = form.querySelector(`input[name="child_id"][value="${childId}"]`);
    
    if (radioInput) {
        radioInput.checked = true;
        
        // Add visual selection
        document.querySelectorAll('.child-avatar-card').forEach(card => {
            card.classList.remove('selected');
        });
        radioInput.closest('.child-avatar-card').classList.add('selected');
        
        // Play selection sound
        if (window.MiniMindsKids) {
            window.MiniMindsKids.playSound('click');
        }
        
        // Submit form
        setTimeout(() => {
            form.submit();
        }, 300);
    }
}

// PIN entry functions
function addPinDigit(digit) {
    if (currentPin.length < pinLength) {
        currentPin += digit;
        updatePinDisplay();
        
        // Play button sound
        if (window.MiniMindsKids) {
            window.MiniMindsKids.playSound('click');
        }
        
        // Add animation to the filled dot
        const dotIndex = currentPin.length - 1;
        const dot = document.getElementById(`pin${dotIndex + 1}`);
        dot.classList.add('pin-filled');
        dot.style.animation = 'pin-pop 0.3s ease';
        
        // Enable login button if PIN is complete
        if (currentPin.length === pinLength) {
            document.getElementById('loginBtn').disabled = false;
            document.getElementById('loginBtn').classList.add('btn-animated');
            
            // Auto-submit after a short delay
            setTimeout(() => {
                if (currentPin.length === pinLength) {
                    submitPin();
                }
            }, 500);
        }
    }
}

function removePinDigit() {
    if (currentPin.length > 0) {
        const removedIndex = currentPin.length;
        currentPin = currentPin.slice(0, -1);
        updatePinDisplay();
        
        // Remove animation from the emptied dot
        const dot = document.getElementById(`pin${removedIndex}`);
        dot.classList.remove('pin-filled');
        
        // Disable login button
        document.getElementById('loginBtn').disabled = true;
        document.getElementById('loginBtn').classList.remove('btn-animated');
        
        // Play sound
        if (window.MiniMindsKids) {
            window.MiniMindsKids.playSound('click');
        }
    }
}

function clearPin() {
    currentPin = '';
    updatePinDisplay();
    
    // Clear all dots
    for (let i = 1; i <= pinLength; i++) {
        const dot = document.getElementById(`pin${i}`);
        dot.classList.remove('pin-filled');
    }
    
    // Disable login button
    document.getElementById('loginBtn').disabled = true;
    document.getElementById('loginBtn').classList.remove('btn-animated');
    
    // Play sound
    if (window.MiniMindsKids) {
        window.MiniMindsKids.playSound('click');
    }
}

function updatePinDisplay() {
    document.getElementById('pinInput').value = currentPin;
}

function submitPin() {
    const form = document.getElementById('pinForm');
    const loginBtn = document.getElementById('loginBtn');
    
    // Show loading state
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging in...';
    
    // Play success sound
    if (window.MiniMindsKids) {
        window.MiniMindsKids.playSound('success');
    }
    
    // Submit form
    form.submit();
}

function goBack() {
    // Clear PIN and go back to child selection
    clearPin();
    window.location.href = 'login.php';
}

function switchToParent() {
    // Redirect to parent dashboard
    window.location.href = '../parents/dashboard.php';
}

// Handle PIN form submission
document.getElementById('pinForm').addEventListener('submit', function(e) {
    if (currentPin.length !== pinLength) {
        e.preventDefault();
        return;
    }
    
    // Form will submit normally
});

// Keyboard support for PIN entry
document.addEventListener('keydown', function(e) {
    if (document.getElementById('pinForm')) {
        if (e.key >= '0' && e.key <= '9') {
            e.preventDefault();
            addPinDigit(e.key);
        } else if (e.key === 'Backspace') {
            e.preventDefault();
            removePinDigit();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            clearPin();
        } else if (e.key === 'Enter' && currentPin.length === pinLength) {
            e.preventDefault();
            submitPin();
        }
    }
});

// Add hover effects to child avatars
document.addEventListener('DOMContentLoaded', function() {
    const childCards = document.querySelectorAll('.child-avatar-card');
    
    childCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.05)';
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(0) scale(1)';
            }
        });
    });
    
    // Initialize animations
    if (window.MiniMindsKids) {
        window.MiniMindsKids.playSound('welcome');
    }
});
</script>

<style>
.kids-login-page {
    min-height: 100vh;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.animated-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float-shape 6s ease-in-out infinite;
}

.shape-1 {
    width: 80px;
    height: 80px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.shape-2 {
    width: 120px;
    height: 120px;
    top: 70%;
    right: 10%;
    animation-delay: 2s;
}

.shape-3 {
    width: 60px;
    height: 60px;
    top: 30%;
    right: 30%;
    animation-delay: 4s;
}

.shape-4 {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 20%;
    animation-delay: 1s;
}

.shape-5 {
    width: 90px;
    height: 90px;
    top: 50%;
    left: 50%;
    animation-delay: 3s;
}

@keyframes float-shape {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.welcome-section {
    color: white;
    text-align: center;
}

.welcome-logo img {
    max-width: 200px;
    height: auto;
    filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
}

.welcome-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.text-bounce {
    display: inline-block;
    animation: bounce 2s ease-in-out infinite;
}

.text-bounce:nth-child(2) {
    animation-delay: 1s;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.welcome-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    font-weight: 500;
}

.kids-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 30px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    overflow: hidden;
    animation: card-appear 0.8s ease-out;
}

@keyframes card-appear {
    0% { transform: scale(0.8) translateY(50px); opacity: 0; }
    100% { transform: scale(1) translateY(0); opacity: 1; }
}

.kids-card-header {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
    padding: 2rem;
    text-align: center;
}

.kids-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.kids-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.kids-card-body {
    padding: 2rem;
}

.children-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.child-avatar-card {
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 1rem;
    border-radius: 20px;
    border: 3px solid transparent;
}

.child-avatar-card:hover {
    background: rgba(102, 126, 234, 0.1);
    transform: translateY(-5px) scale(1.05);
}

.child-avatar-card.selected {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
    border-color: var(--primary-color);
    transform: translateY(-5px) scale(1.05);
}

.child-avatar-label {
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.avatar-container {
    position: relative;
    margin-bottom: 1rem;
}

.child-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.avatar-level-badge {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: linear-gradient(135deg, #feca57, #ff9ff3);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-weight: 600;
    font-size: 0.8rem;
    box-shadow: 0 5px 15px rgba(254, 202, 87, 0.4);
}

.child-name {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.child-stats {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.stat-badge {
    background: linear-gradient(135deg, #48dbfb, #0abde3);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
}

.pin-display {
    text-align: center;
    margin-bottom: 2rem;
}

.pin-dots {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.pin-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #667eea;
    background: white;
    transition: all 0.3s ease;
}

.pin-dot.pin-filled {
    background: linear-gradient(135deg, #feca57, #ff9ff3);
    border-color: #feca57;
    transform: scale(1.2);
}

@keyframes pin-pop {
    0% { transform: scale(1); }
    50% { transform: scale(1.5); }
    100% { transform: scale(1.2); }
}

.pin-keypad {
    max-width: 300px;
    margin: 0 auto 2rem;
}

.keypad-row {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    justify-content: center;
}

.pin-btn {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.pin-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.pin-btn:active {
    transform: translateY(0);
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}

.pin-clear {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    font-size: 1rem;
}

.pin-backspace {
    background: linear-gradient(135deg, #feca57, #ff9ff3);
}

.pin-actions {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.pin-actions .btn {
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-animated {
    animation: pulse-success 1s ease-in-out infinite;
}

@keyframes pulse-success {
    0%, 100% { box-shadow: 0 5px 15px rgba(72, 187, 120, 0.3); }
    50% { box-shadow: 0 8px 25px rgba(72, 187, 120, 0.5); }
}

.pin-help {
    text-align: center;
    color: #6c757d;
    font-size: 0.9rem;
}

.kids-alert {
    border-radius: 20px;
    border: none;
    backdrop-filter: blur(10px);
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-emoji {
    font-size: 1.5rem;
}

.no-children-message {
    text-align: center;
    padding: 3rem;
    color: var(--dark-color);
}

.parent-switch-btn {
    border-radius: 25px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.parent-switch-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: white;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .welcome-title {
        font-size: 2rem;
    }
    
    .welcome-logo img {
        max-width: 150px;
    }
    
    .kids-card {
        margin: 1rem;
        border-radius: 20px;
    }
    
    .kids-card-header,
    .kids-card-body {
        padding: 1.5rem;
    }
    
    .kids-title {
        font-size: 1.5rem;
    }
    
    .children-grid {
        grid-template-columns: 1fr;
    }
    
    .child-avatar {
        width: 100px;
        height: 100px;
    }
    
    .pin-btn {
        width: 70px;
        height: 70px;
        font-size: 1.3rem;
    }
    
    .pin-actions {
        flex-direction: column;
    }
}
</style>

<?php include '../includes/footer.php'; ?>