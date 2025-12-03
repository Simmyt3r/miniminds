<?php
/**
 * MiniMinds Academy - Helper Functions
 * Core utility functions for authentication, formatting, and platform operations
 */

require_once 'config.php';

/**
 * Authentication & Session Management
 */

/**
 * Check if user is logged in (middleware protection)
 * Redirects to login page if not authenticated
 */
function checkLogin($required_role = null) {
    session_start();
    
    // Check if user session exists
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['child_id'])) {
        // Store current page for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        if ($required_role === 'child') {
            header('Location: ../kids/login.php');
        } else {
            header('Location: ../parents/login.php');
        }
        exit();
    }
    
    // Check role requirements
    if ($required_role) {
        if ($required_role === 'parent' && !isParent()) {
            header('Location: ../kids/login.php');
            exit();
        } elseif ($required_role === 'child' && !isChild()) {
            header('Location: ../parents/login.php');
            exit();
        }
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        header('Location: ../parents/login.php?timeout=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Check if current user is a parent
 */
function isParent() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'parent';
}

/**
 * Check if current user is a child
 */
function isChild() {
    return isset($_SESSION['child_id']);
}

/**
 * Get current user information
 */
function getCurrentUser() {
    if (isParent()) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } elseif (isChild()) {
        $db = getDB();
        $stmt = $db->prepare("SELECT c.*, u.full_name as parent_name FROM children c JOIN users u ON c.parent_id = u.id WHERE c.id = ?");
        $stmt->execute([$_SESSION['child_id']]);
        return $stmt->fetch();
    }
    return null;
}

/**
 * Get children for current parent
 */
function getParentChildren($parent_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM children WHERE parent_id = ? AND is_active = 1 ORDER BY created_at");
    $stmt->execute([$parent_id]);
    return $stmt->fetchAll();
}

/**
 * Verify child PIN
 */
function verifyChildPIN($child_id, $pin) {
    $db = getDB();
    $stmt = $db->prepare("SELECT pin_code FROM children WHERE id = ? AND is_active = 1");
    $stmt->execute([$child_id]);
    $child = $stmt->fetch();
    
    return $child && hash_equals($child['pin_code'], $pin);
}

/**
 * Update child progress
 */
function updateChildProgress($child_id, $lesson_id, $status, $completion_percentage = 0) {
    $db = getDB();
    
    // Check if progress record exists
    $stmt = $db->prepare("SELECT id FROM progress WHERE child_id = ? AND lesson_id = ?");
    $stmt->execute([$child_id, $lesson_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing record
        $stmt = $db->prepare("UPDATE progress SET status = ?, completion_percentage = ?, last_updated = NOW() WHERE child_id = ? AND lesson_id = ?");
        $stmt->execute([$status, $completion_percentage, $child_id, $lesson_id]);
    } else {
        // Create new record
        $stmt = $db->prepare("INSERT INTO progress (child_id, lesson_id, status, completion_percentage, started_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$child_id, $lesson_id, $status, $completion_percentage]);
    }
    
    // Award XP and points for completion
    if ($status === 'completed') {
        awardLessonRewards($child_id, $lesson_id);
    }
}

/**
 * Award XP and points for lesson completion
 */
function awardLessonRewards($child_id, $lesson_id) {
    $db = getDB();
    
    // Get lesson rewards
    $stmt = $db->prepare("SELECT xp_reward, points_reward FROM lessons WHERE id = ?");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch();
    
    if ($lesson) {
        // Update child's total XP and points
        $stmt = $db->prepare("UPDATE children SET total_points = total_points + ?, current_xp = current_xp + ? WHERE id = ?");
        $stmt->execute([$lesson['points_reward'], $lesson['xp_reward'], $child_id]);
        
        // Check for level up
        checkLevelUp($child_id);
        
        // Check for achievements
        checkAchievements($child_id);
    }
}

/**
 * Check if child should level up
 */
function checkLevelUp($child_id) {
    $db = getDB();
    
    // Get current child stats
    $stmt = $db->prepare("SELECT current_xp, current_level FROM children WHERE id = ?");
    $stmt->execute([$child_id]);
    $child = $stmt->fetch();
    
    if ($child && $child['current_xp'] >= POINTS_PER_LEVEL) {
        $new_level = $child['current_level'] + 1;
        $new_xp = $child['current_xp'] - POINTS_PER_LEVEL;
        
        // Update level and reset XP
        $stmt = $db->prepare("UPDATE children SET current_level = ?, current_xp = ? WHERE id = ?");
        $stmt->execute([$new_level, $new_xp, $child_id]);
        
        // Award level up bonus
        $stmt = $db->prepare("UPDATE children SET total_points = total_points + 50 WHERE id = ?");
        $stmt->execute([$child_id]);
    }
}

/**
 * Check and award achievements
 */
function checkAchievements($child_id) {
    $db = getDB();
    
    // Get child's stats
    $stmt = $db->prepare("SELECT c.*, COUNT(DISTINCT p.lesson_id) as completed_lessons FROM children c LEFT JOIN progress p ON c.id = p.child_id AND p.status = 'completed' WHERE c.id = ?");
    $stmt->execute([$child_id]);
    $child = $stmt->fetch();
    
    // Check for completion achievements
    $achievements_to_check = [
        1 => ['requirement' => 1, 'type' => 'completion'], // First Steps
        2 => ['requirement' => 5, 'type' => 'completion'], // Quick Learner
        3 => ['requirement' => 10, 'type' => 'completion'], // Star Student
        4 => ['requirement' => 100, 'type' => 'milestone', 'field' => 'total_points'], // Point Collector
        5 => ['requirement' => 200, 'type' => 'milestone', 'field' => 'current_xp'] // XP Master
    ];
    
    foreach ($achievements_to_check as $achievement_id => $criteria) {
        // Check if already earned
        $stmt = $db->prepare("SELECT id FROM child_achievements WHERE child_id = ? AND achievement_id = ?");
        $stmt->execute([$child_id, $achievement_id]);
        if ($stmt->fetch()) continue;
        
        // Check if criteria met
        $met = false;
        if ($criteria['type'] === 'completion') {
            $met = $child['completed_lessons'] >= $criteria['requirement'];
        } elseif ($criteria['type'] === 'milestone') {
            $field = $criteria['field'];
            $met = $child[$field] >= $criteria['requirement'];
        }
        
        if ($met) {
            // Award achievement
            $stmt = $db->prepare("INSERT INTO child_achievements (child_id, achievement_id) VALUES (?, ?)");
            $stmt->execute([$child_id, $achievement_id]);
            
            // Get achievement rewards
            $stmt = $db->prepare("SELECT points_reward, xp_reward FROM achievements WHERE id = ?");
            $stmt->execute([$achievement_id]);
            $achievement = $stmt->fetch();
            
            if ($achievement) {
                // Award bonus rewards
                $stmt = $db->prepare("UPDATE children SET total_points = total_points + ?, current_xp = current_xp + ? WHERE id = ?");
                $stmt->execute([$achievement['points_reward'], $achievement['xp_reward'], $child_id]);
            }
        }
    }
}

/**
 * Formatting Functions
 */

/**
 * Format money in Nigerian Naira
 */
function formatMoney($amount) {
    return '₦' . number_format($amount, 2);
}

/**
 * Format time duration (minutes to readable format)
 */
function formatDuration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min' . ($minutes !== 1 ? 's' : '');
    }
    $hours = floor($minutes / 60);
    $remaining_minutes = $minutes % 60;
    
    $output = $hours . ' hr' . ($hours !== 1 ? 's' : '');
    if ($remaining_minutes > 0) {
        $output .= ' and ' . $remaining_minutes . ' min' . ($remaining_minutes !== 1 ? 's' : '');
    }
    return $output;
}

/**
 * Activity Logging
 */

/**
 * Send notification email (placeholder function)
 */
function sendNotificationEmail($to, $subject, $message) {
    // In production, implement actual email sending
    error_log("Email to: $to, Subject: $subject, Message: $message");
    return true;
}

/**
 * Log user activity
 *
 * NOTE: The parameter order has been fixed to comply with modern PHP standards:
 * all required parameters come first, followed by optional parameters.
 * We are making both $user_id and $child_id optional (with null default) 
 * as one of them might be null, based on how the function is used.
 */
function logActivity($activity, $details = '', $user_id = null, $child_id = null) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO activity_log (user_id, child_id, activity, details, ip_address, user_agent, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $user_id,
        $child_id,
        $activity,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Create activity log table if not exists
 */
function ensureActivityLogTable() {
    $db = getDB();
    $sql = "
        CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            child_id INT,
            activity VARCHAR(100) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE SET NULL,
            INDEX idx_activity_user_child (user_id, child_id),
            INDEX idx_activity_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $db->exec($sql);
}

/**
 * Error Handling Utility
 */
function setCustomErrorHandler() {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        // Only log errors that match the error reporting level
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        // Log the error
        $error_message = "PHP Error: [$errno] $errstr in $errfile on line $errline";
        error_log($error_message);
        
        // If it's a critical error, stop execution
        if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR) {
            // Show a generic error message in production
            if (ENVIRONMENT === 'production') {
                http_response_code(500);
                exit('A critical system error occurred.');
            } else {
                // Show detailed error in development
                return false; // Use PHP's default error handler
            }
        }
        
        // Don't execute PHP's internal error handler
        return true;
    });
}

// ensureActivityLogTable(); // Should be run once during initial setup.
// setCustomErrorHandler(); // Uncomment this line to enable custom error handler