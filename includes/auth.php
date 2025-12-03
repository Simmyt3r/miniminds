<?php
/**
 * MiniMinds Academy - Authentication Controller
 * Handles parent registration, login, and child authentication
 */

require_once 'config.php';
require_once 'functions.php';

/**
 * Handle Parent Registration
 */
function handleParentRegistration() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request method'];
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => 'Invalid security token'];
    }
    
    // Get and sanitize form data
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    // Validate required fields
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        return ['success' => false, 'message' => 'All required fields must be filled'];
    }
    
    // Validate email format
    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    
    // Check password confirmation
    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Passwords do not match'];
    }
    
    // Check if login is locked due to too many attempts
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isLoginLocked($ip)) {
        return ['success' => false, 'message' => 'Too many registration attempts. Please try again later.'];
    }
    
    try {
        $db = getDB();
        
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            recordLoginAttempt($ip);
            return ['success' => false, 'message' => 'Username already taken'];
        }
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            recordLoginAttempt($ip);
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password securely
        $password_hash = hashPassword($password);
        
        // Insert new user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, full_name, phone, role, created_at) 
            VALUES (?, ?, ?, ?, ?, 'parent', NOW())
        ");
        $stmt->execute([$username, $email, $password_hash, $full_name, $phone]);
        
        $user_id = $db->lastInsertId();
        
        // Log successful registration
        logActivity($user_id, null, 'parent_registration', "New parent registered: $username");
        
        // Send welcome email
        sendNotificationEmail($email, 'Welcome to MiniMinds Academy!', "Welcome $full_name! Your account has been created successfully.");
        
        return ['success' => true, 'message' => 'Registration successful! Please login.', 'user_id' => $user_id];
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Handle Parent Login
 */
function handleParentLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request method'];
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => 'Invalid security token'];
    }
    
    // Get and sanitize form data
    $login_identifier = sanitizeInput($_POST['login_identifier'] ?? ''); // Can be username or email
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate required fields
    if (empty($login_identifier) || empty($password)) {
        return ['success' => false, 'message' => 'Please enter your username/email and password'];
    }
    
    // Check if login is locked due to too many attempts
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isLoginLocked($ip)) {
        return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
    }
    
    try {
        $db = getDB();
        
        // Find user by username or email
        $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'parent' AND is_active = 1");
        $stmt->execute([$login_identifier, $login_identifier]);
        $user = $stmt->fetch();
        
        if (!$user) {
            recordLoginAttempt($ip);
            return ['success' => false, 'message' => 'Invalid username/email or password'];
        }
        
        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            recordLoginAttempt($ip);
            return ['success' => false, 'message' => 'Invalid username/email or password'];
        }
        
        // Check subscription status
        if ($user['subscription_expiry'] && strtotime($user['subscription_expiry']) < time()) {
            // Expired subscription, downgrade to free
            $stmt = $db->prepare("UPDATE users SET subscription_plan = 'free', subscription_expiry = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user['subscription_plan'] = 'free';
        }
        
        // Create session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['subscription_plan'] = $user['subscription_plan'];
        $_SESSION['last_activity'] = time();
        
        // Update last login timestamp
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Log successful login
        logActivity($user['id'], null, 'parent_login', "Parent logged in: {$user['username']}");
        
        // Set remember me cookie if requested
        if ($remember_me) {
            $token = generateSecureToken(64);
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            
            // Store remember token in database
            $stmt = $db->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', $expires)]);
            
            setcookie('remember_token', $token, $expires, '/', '', false, true);
        }
        
        return ['success' => true, 'message' => 'Login successful!', 'redirect' => 'dashboard.php'];
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

/**
 * Handle Child Login
 */
function handleChildLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request method'];
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => 'Invalid security token'];
    }
    
    // Get and sanitize form data
    $child_id = intval($_POST['child_id'] ?? 0);
    $pin_code = $_POST['pin_code'] ?? '';
    
    // Validate required fields
    if ($child_id <= 0 || empty($pin_code)) {
        return ['success' => false, 'message' => 'Please select a child and enter PIN'];
    }
    
    // Check if login is locked due to too many attempts
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isLoginLocked($ip)) {
        return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
    }
    
    try {
        $db = getDB();
        
        // Get child details
        $stmt = $db->prepare("SELECT * FROM children WHERE id = ? AND is_active = 1");
        $stmt->execute([$child_id]);
        $child = $stmt->fetch();
        
        if (!$child) {
            recordLoginAttempt($ip);
            return ['success' => false, 'message' => 'Invalid child profile'];
        }
        
        // Verify PIN
        if (!verifyChildPIN($child_id, $pin_code)) {
            recordLoginAttempt($ip);
            return ['success' => false, 'message' => 'Invalid PIN code'];
        }
        
        // Create child session
        session_regenerate_id(true);
        $_SESSION['child_id'] = $child['id'];
        $_SESSION['child_username'] = $child['username'];
        $_SESSION['child_display_name'] = $child['display_name'];
        $_SESSION['child_age'] = $child['age'];
        $_SESSION['child_avatar'] = $child['avatar_url'];
        $_SESSION['child_level'] = $child['current_level'];
        $_SESSION['child_xp'] = $child['current_xp'];
        $_SESSION['child_points'] = $child['total_points'];
        $_SESSION['last_activity'] = time();
        
        // Update last active timestamp
        $stmt = $db->prepare("UPDATE children SET last_active = NOW() WHERE id = ?");
        $stmt->execute([$child['id']]);
        
        // Check for daily login bonus
        awardDailyLoginBonus($child['id']);
        
        // Log successful login
        logActivity(null, $child['id'], 'child_login', "Child logged in: {$child['display_name']}");
        
        return ['success' => true, 'message' => 'Welcome back!', 'redirect' => 'dashboard.php'];
        
    } catch (Exception $e) {
        error_log("Child login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

/**
 * Award daily login bonus for child
 */
function awardDailyLoginBonus($child_id) {
    $db = getDB();
    
    // Check if already received bonus today
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM activity_log WHERE child_id = ? AND activity = 'daily_bonus' AND DATE(created_at) = CURDATE()");
    $stmt->execute([$child_id]);
    $already_received = $stmt->fetch()['count'] > 0;
    
    if (!$already_received) {
        // Award login bonus
        $bonus_points = 5;
        $bonus_xp = 10;
        
        $stmt = $db->prepare("UPDATE children SET total_points = total_points + ?, current_xp = current_xp + ? WHERE id = ?");
        $stmt->execute([$bonus_points, $bonus_xp, $child_id]);
        
        checkLevelUp($child_id);
        
        // Log the bonus
        logActivity(null, $child_id, 'daily_bonus', "Daily login bonus: +$bonus_points points, +$bonus_xp XP");
    }
}

/**
 * Handle Logout (for both parent and child)
 */
function handleLogout() {
    // Log logout activity
    if (isParent()) {
        logActivity($_SESSION['user_id'], null, 'parent_logout', "Parent logged out: {$_SESSION['username']}");
    } elseif (isChild()) {
        logActivity(null, $_SESSION['child_id'], 'child_logout', "Child logged out: {$_SESSION['child_display_name']}");
    }
    
    // Destroy session
    session_destroy();
    
    // Clear remember me cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['remember_token']);
    }
    
    return ['success' => true, 'message' => 'Logged out successfully'];
}

/**
 * Check remember me token for auto-login
 */
function checkRememberMeToken() {
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $token = $_COOKIE['remember_token'];
    
    try {
        $db = getDB();
        
        // Find valid token
        $stmt = $db->prepare("
            SELECT u.* FROM users u 
            JOIN remember_tokens rt ON u.id = rt.user_id 
            WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Invalid or expired token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            return false;
        }
        
        // Auto-login user
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['subscription_plan'] = $user['subscription_plan'];
        $_SESSION['last_activity'] = time();
        
        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Log auto-login
        logActivity($user['id'], null, 'auto_login', "Auto-login via remember token: {$user['username']}");
        
        return true;
        
    } catch (Exception $e) {
        error_log("Remember me token error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add Child Profile (for parents)
 */
function handleAddChild() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request method'];
    }
    
    // Check if parent is logged in
    if (!isParent()) {
        return ['success' => false, 'message' => 'Authentication required'];
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => 'Invalid security token'];
    }
    
    // Get and sanitize form data
    $parent_id = $_SESSION['user_id'];
    $username = sanitizeInput($_POST['username'] ?? '');
    $display_name = sanitizeInput($_POST['display_name'] ?? '');
    $pin_code = $_POST['pin_code'] ?? '';
    $confirm_pin = $_POST['confirm_pin'] ?? '';
    $age = intval($_POST['age'] ?? 0);
    
    // Validate required fields
    if (empty($username) || empty($display_name) || empty($pin_code) || empty($confirm_pin)) {
        return ['success' => false, 'message' => 'All required fields must be filled'];
    }
    
    // Validate age range
    if ($age < MIN_AGE || $age > MAX_AGE) {
        return ['success' => false, 'message' => "Age must be between " . MIN_AGE . " and " . MAX_AGE];
    }
    
    // Validate PIN format (4 digits)
    if (!preg_match('/^\d{4}$/', $pin_code)) {
        return ['success' => false, 'message' => 'PIN must be exactly 4 digits'];
    }
    
    // Check PIN confirmation
    if ($pin_code !== $confirm_pin) {
        return ['success' => false, 'message' => 'PIN codes do not match'];
    }
    
    try {
        $db = getDB();
        
        // Check parent's subscription limit
        $stmt = $db->prepare("SELECT subscription_plan FROM users WHERE id = ?");
        $stmt->execute([$parent_id]);
        $parent = $stmt->fetch();
        
        $stmt = $db->prepare("SELECT COUNT(*) as child_count FROM children WHERE parent_id = ? AND is_active = 1");
        $stmt->execute([$parent_id]);
        $child_count = $stmt->fetch()['child_count'];
        
        $max_children = getMaxChildrenAllowed($parent['subscription_plan']);
        if ($child_count >= $max_children) {
            return ['success' => false, 'message' => "You've reached the maximum number of children for your plan. Upgrade to add more."];
        }
        
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM children WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already taken'];
        }
        
        // Insert new child
        $stmt = $db->prepare("
            INSERT INTO children (parent_id, username, display_name, pin_code, age, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$parent_id, $username, $display_name, $pin_code]);
        
        $child_id = $db->lastInsertId();
        
        // Log child addition
        logActivity($parent_id, null, 'child_added', "Parent added child: $username (Age: $age)");
        
        return ['success' => true, 'message' => 'Child profile created successfully!', 'child_id' => $child_id];
        
    } catch (Exception $e) {
        error_log("Add child error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create child profile. Please try again.'];
    }
}

/**
 * Get maximum children allowed based on subscription plan
 */
function getMaxChildrenAllowed($plan) {
    switch ($plan) {
        case 'free': return 1;
        case 'premium': return 2;
        case 'family': return 5;
        default: return 1;
    }
}

/**
 * API endpoint handlers
 */
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'parent_register':
            echo json_encode(handleParentRegistration());
            break;
        case 'parent_login':
            echo json_encode(handleParentLogin());
            break;
        case 'child_login':
            echo json_encode(handleChildLogin());
            break;
        case 'add_child':
            echo json_encode(handleAddChild());
            break;
        case 'logout':
            echo json_encode(handleLogout());
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>