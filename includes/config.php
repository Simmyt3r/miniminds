<?php
/**
 * MiniMinds Academy - Configuration File
 * Secure PHP configuration for gamified learning platform
 */

// Start session for user authentication
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'miniminds_academy');
define('DB_USER', 'root');
define('DB_PASS', '');

// Platform Configuration Constants
define('SITE_NAME', 'MiniMinds Academy');
define('SITE_URL', 'http://localhost/miniminds-academy');
define('VERSION', '1.0.0');

// Child Safety & Age Restrictions
define('MIN_AGE', 4);
define('MAX_AGE', 9);
define('PARENT_VERIFICATION_REQUIRED', true);
define('CHAT_FILTER_ENABLED', true);
define('MAX_DAILY_TIME_MINUTES', 120);

// Gamification Settings
define('POINTS_PER_LEVEL', 100);
define('DAILY_QUEST_POINTS', 20);
define('WEEKLY_QUEST_POINTS', 100);
define('LOGIN_STREAK_REWARD', 5);

// Security Settings
define('HASH_COST', 12);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Subscription Plans (Nigerian Naira)
define('FREE_PLAN_PRICE', 0);
define('PREMIUM_PLAN_PRICE', 500);
define('FAMILY_PLAN_PRICE', 1500);

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Email Configuration (for notifications)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@miniminds.com');
define('FROM_NAME', 'MiniMinds Academy');

/**
 * Secure Database Connection using PDO
 * Prevents SQL injection with prepared statements
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error but don't expose details to user
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute prepared statement securely
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database error occurred.");
        }
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

/**
 * Security Helper Functions
 */

/**
 * Generate secure random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize input to prevent XSS
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * CSRF Token Generation and Validation
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateSecureToken();
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Token expires after 1 hour
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting for login attempts
 */
function isLoginLocked($ip) {
    $attempts_file = __DIR__ . '/../logs/login_attempts.json';
    $attempts = [];
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
    }
    
    $current_time = time();
    $recent_attempts = array_filter($attempts, function($attempt) use ($ip, $current_time) {
        return $attempt['ip'] === $ip && ($current_time - $attempt['time']) < LOGIN_LOCKOUT_TIME;
    });
    
    return count($recent_attempts) >= MAX_LOGIN_ATTEMPTS;
}

function recordLoginAttempt($ip) {
    $attempts_file = __DIR__ . '/../logs/login_attempts.json';
    $attempts = [];
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
    }
    
    $attempts[] = [
        'ip' => $ip,
        'time' => time()
    ];
    
    // Keep only last 100 attempts
    $attempts = array_slice($attempts, -100);
    
    file_put_contents($attempts_file, json_encode($attempts), LOCK_EX);
}

/**
 * Set secure headers
 */
function setSecurityHeaders() {
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net;");
}

// Apply security headers to all requests
setSecurityHeaders();

// Initialize database connection
$db = Database::getInstance();

// Helper function to get database instance
function getDB() {
    global $db;
    return $db->getConnection();
}
?>