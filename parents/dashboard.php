<?php
/**
 * MiniMinds Academy - Parent Dashboard
 * Main dashboard for parents to monitor children's progress
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Protect page - only parents can access
checkLogin('parent');

$page_title = 'Parent Dashboard - MiniMinds Academy';
$current_user = getCurrentUser();
$children = getParentChildren($_SESSION['user_id']);

// Get dashboard statistics
$db = getDB();

// Total children
$total_children = count($children);

// Children active today
$stmt = $db->prepare("SELECT COUNT(*) as count FROM children WHERE parent_id = ? AND DATE(last_active) = CURDATE()");
$stmt->execute([$_SESSION['user_id']]);
$active_today = $stmt->fetch()['count'];

// Total points earned by all children
$stmt = $db->prepare("SELECT SUM(total_points) as total FROM children WHERE parent_id = ? AND is_active = 1");
$stmt->execute([$_SESSION['user_id']]);
$total_points = $stmt->fetch()['total'] ?? 0;

// Lessons completed this week
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT p.lesson_id) as count 
    FROM progress p 
    JOIN children c ON p.child_id = c.id 
    WHERE c.parent_id = ? AND p.status = 'completed' AND p.completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmt->execute([$_SESSION['user_id']]);
$lessons_this_week = $stmt->fetch()['count'];

// Get recent activities
$stmt = $db->prepare("
    SELECT al.*, c.display_name as child_name 
    FROM activity_log al 
    LEFT JOIN children c ON al.child_id = c.id 
    WHERE al.user_id = ? OR (al.child_id IN (SELECT id FROM children WHERE parent_id = ?))
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$recent_activities = $stmt->fetchAll();

// Get subscription status
$subscription_status = $_SESSION['subscription_plan'];
$subscription_info = getSubscriptionInfo($subscription_status);

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2">Welcome back, <?php echo htmlspecialchars($current_user['full_name']); ?>! 👋</h1>
                            <p class="mb-0 opacity-75">
                                Here's what's happening with your children's learning journey today.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="subscription-badge">
                                <span class="badge bg-<?php echo $subscription_info['color']; ?> fs-6">
                                    <i class="fas fa-<?php echo $subscription_info['icon']; ?> me-2"></i>
                                    <?php echo ucfirst($subscription_status); ?> Plan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="stats-icon bg-primary bg-gradient">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $total_children; ?></h3>
                    <p class="stats-label text-muted">Children</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="stats-icon bg-success bg-gradient">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $lessons_this_week; ?></h3>
                    <p class="stats-label text-muted">Lessons This Week</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="stats-icon bg-warning bg-gradient">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h3 class="stats-number"><?php echo number_format($total_points); ?></h3>
                    <p class="stats-label text-muted">Total Points Earned</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="stats-icon bg-info bg-gradient">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $active_today; ?></h3>
                    <p class="stats-label text-muted">Active Today</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Children Section -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Your Children
                    </h5>
                    <?php if ($total_children < getMaxChildrenAllowed($subscription_status)): ?>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addChildModal">
                        <i class="fas fa-plus me-1"></i> Add Child
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($children): ?>
                    <div class="row">
                        <?php foreach ($children as $child): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card child-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/avatars/<?php echo htmlspecialchars($child['avatar_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($child['display_name']); ?>" 
                                             class="rounded-circle me-3" width="60" height="60">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($child['display_name']); ?></h6>
                                            <small class="text-muted">Age <?php echo $child['age']; ?> • Level <?php echo $child['current_level']; ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-info mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Level Progress</small>
                                            <small class="text-muted"><?php echo $child['current_xp']; ?>/<?php echo POINTS_PER_LEVEL; ?> XP</small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo min(100, ($child['current_xp'] / POINTS_PER_LEVEL) * 100); ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-warning">
                                            <i class="fas fa-coins me-1"></i>
                                            <?php echo number_format($child['total_points']); ?> Points
                                        </span>
                                        <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm view-child-btn" data-child-id="<?php echo $child['id']; ?>">
                                            View Details
                                        </a>
                                    </div>
                                    
                                    <?php if ($child['last_active']): ?>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-clock me-1"></i>
                                        Last active: <?php echo timeAgo($child['last_active']); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-child fa-3x text-muted mb-3"></i>
                        <h5>No children added yet</h5>
                        <p class="text-muted">Add your first child to start their learning adventure!</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChildModal">
                            <i class="fas fa-plus me-1"></i> Add Your First Child
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activity & Subscription Section -->
        <div class="col-lg-4">
            <!-- Recent Activities -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($recent_activities): ?>
                    <div class="activity-feed">
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item mb-3">
                            <div class="d-flex">
                                <div class="activity-icon">
                                    <?php echo getActivityIcon($activity['activity']); ?>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-text">
                                        <?php if ($activity['child_name']): ?>
                                        <strong><?php echo htmlspecialchars($activity['child_name']); ?></strong> 
                                        <?php endif; ?>
                                        <?php echo getActivityDescription($activity['activity']); ?>
                                    </div>
                                    <small class="text-muted"><?php echo timeAgo($activity['created_at']); ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center">No recent activities</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Subscription Status -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-crown me-2"></i>
                        Subscription Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="subscription-plan-icon mb-2">
                            <i class="fas fa-<?php echo $subscription_info['icon']; ?> fa-3x text-<?php echo $subscription_info['color']; ?>"></i>
                        </div>
                        <h6 class="text-uppercase"><?php echo ucfirst($subscription_status); ?> Plan</h6>
                        <p class="text-muted"><?php echo $subscription_info['description']; ?></p>
                    </div>
                    
                    <div class="plan-features mb-3">
                        <small class="text-muted">
                            <div class="mb-1">
                                <i class="fas fa-child me-2"></i>
                                <?php echo getMaxChildrenAllowed($subscription_status); ?> Children
                            </div>
                            <div class="mb-1">
                                <i class="fas fa-book me-2"></i>
                                <?php echo $subscription_status === 'free' ? '5' : 'Unlimited'; ?> Lessons
                            </div>
                            <div class="mb-1">
                                <i class="fas fa-chart-line me-2"></i>
                                <?php echo $subscription_status === 'free' ? 'Basic' : 'Advanced'; ?> Reports
                            </div>
                        </small>
                    </div>
                    
                    <?php if ($subscription_status === 'free'): ?>
                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#upgradeModal">
                        <i class="fas fa-arrow-up me-1"></i> Upgrade to Premium
                    </button>
                    <?php elseif ($subscription_status === 'premium'): ?>
                    <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#upgradeModal">
                        <i class="fas fa-users me-1"></i> Upgrade to Family Plan
                    </button>
                    <?php else: ?>
                    <button class="btn btn-outline-secondary w-100">
                        <i class="fas fa-check me-1"></i> Current Plan
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Child Modal -->
<div class="modal fade" id="addChildModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Child</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../includes/auth.php" id="addChildForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_child">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <small class="text-muted">This will be used for login</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Display Name *</label>
                        <input type="text" class="form-control" id="display_name" name="display_name" required>
                        <small class="text-muted">This is how the name will appear in the app</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="age" class="form-label">Age *</label>
                        <select class="form-control" id="age" name="age" required>
                            <?php for ($i = MIN_AGE; $i <= MAX_AGE; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> years</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pin_code" class="form-label">4-Digit PIN *</label>
                        <input type="password" class="form-control" id="pin_code" name="pin_code" 
                               maxlength="4" pattern="[0-9]{4}" required>
                        <small class="text-muted">Simple PIN for child login</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_pin" class="form-label">Confirm PIN *</label>
                        <input type="password" class="form-control" id="confirm_pin" name="confirm_pin" 
                               maxlength="4" pattern="[0-9]{4}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Child</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upgrade Subscription Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upgrade Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-crown fa-4x text-warning mb-3"></i>
                    <h5>Choose Your Plan</h5>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="text-primary">Premium</h6>
                                <h4><?php echo formatMoney(PREMIUM_PLAN_PRICE); ?>/month</h4>
                                <small class="text-muted">2 Children • Unlimited Lessons</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-success">Family</h6>
                                <h4><?php echo formatMoney(FAMILY_PLAN_PRICE); ?>/month</h4>
                                <small class="text-muted">5 Children • All Features</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Secure payment via Paystack. Cancel anytime.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Maybe Later</button>
                <button type="button" class="btn btn-success">Proceed to Payment</button>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';

/**
 * Helper functions for dashboard
 */
function getSubscriptionInfo($plan) {
    $plans = [
        'free' => [
            'color' => 'secondary',
            'icon' => 'seedling',
            'description' => 'Basic features with 1 child'
        ],
        'premium' => [
            'color' => 'primary',
            'icon' => 'star',
            'description' => 'Enhanced features with 2 children'
        ],
        'family' => [
            'color' => 'success',
            'icon' => 'crown',
            'description' => 'All features with up to 5 children'
        ]
    ];
    
    return $plans[$plan] ?? $plans['free'];
}

function getActivityIcon($activity) {
    $icons = [
        'parent_login' => '<i class="fas fa-sign-in-alt text-primary"></i>',
        'parent_logout' => '<i class="fas fa-sign-out-alt text-secondary"></i>',
        'child_login' => '<i class="fas fa-child text-success"></i>',
        'child_logout' => '<i class="fas fa-child text-muted"></i>',
        'lesson_completed' => '<i class="fas fa-check-circle text-success"></i>',
        'achievement_earned' => '<i class="fas fa-trophy text-warning"></i>',
        'level_up' => '<i class="fas fa-arrow-up text-info"></i>',
        'daily_bonus' => '<i class="fas fa-gift text-primary"></i>',
        'child_added' => '<i class="fas fa-user-plus text-info"></i>'
    ];
    
    return $icons[$activity] ?? '<i class="fas fa-circle text-muted"></i>';
}

function getActivityDescription($activity) {
    $descriptions = [
        'parent_login' => 'logged in to parent dashboard',
        'parent_logout' => 'logged out from parent dashboard',
        'child_login' => 'logged in to start learning',
        'child_logout' => 'finished learning session',
        'lesson_completed' => 'completed a lesson',
        'achievement_earned' => 'earned a new achievement',
        'level_up' => 'leveled up!',
        'daily_bonus' => 'received daily login bonus',
        'child_added' => 'was added to the family'
    ];
    
    return $descriptions[$activity] ?? 'performed an activity';
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M j, Y', $time);
}
?>