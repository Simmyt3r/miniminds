<?php
/**
 * MiniMinds Academy - Kid Dashboard
 * Fun, colorful dashboard for children with gamification elements
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Protect page - only children can access
checkLogin('child');

$page_title = 'My Learning Adventure - MiniMinds Academy';

// Get child's current data
$child_data = getCurrentUser();
$courses = getAvailableCourses($child_data['age']);
$daily_quests = getDailyQuests($child_data['id']);
$recent_achievements = getRecentAchievements($child_data['id'], 3);

// Calculate level progress percentage
$level_progress = min(100, ($child_data['current_xp'] / POINTS_PER_LEVEL) * 100);

// Check for login streak
$db = getDB();
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT DATE(created_at)) as streak_days 
    FROM activity_log 
    WHERE child_id = ? AND activity = 'child_login' 
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$stmt->execute([$child_data['id']]);
$login_streak = $stmt->fetch()['streak_days'];

// Get today's completed lessons count
$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM progress 
    WHERE child_id = ? AND status = 'completed' AND DATE(completed_at) = CURDATE()
");
$stmt->execute([$child_data['id']]);
$lessons_today = $stmt->fetch()['count'];

include '../includes/header.php';
?>

<div class="container-fluid kids-dashboard">
    <!-- Welcome Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-section">
                <div class="hero-content">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="welcome-text">
                                <h1 class="animated-title">
                                    <span class="wave-emoji">👋</span>
                                    Hi, <?php echo htmlspecialchars($child_data['display_name']); ?>!
                                </h1>
                                <p class="lead animated-subtitle">
                                    Ready for today's learning adventure? Let's play and learn together! 🎮
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="avatar-container">
                                <img src="<?php echo SITE_URL; ?>/assets/images/avatars/<?php echo htmlspecialchars($child_data['avatar_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($child_data['display_name']); ?>" 
                                     class="main-avatar">
                                <div class="level-badge">
                                    Level <?php echo $child_data['current_level']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress & Stats Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stats-bar">
                <div class="row align-items-center">
                    <!-- XP Progress -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="progress-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="progress-label">
                                    <i class="fas fa-bolt text-warning"></i>
                                    Level Progress
                                </span>
                                <span class="progress-value">
                                    <?php echo $child_data['current_xp']; ?> / <?php echo POINTS_PER_LEVEL; ?> XP
                                </span>
                            </div>
                            <div class="progress xp-progress">
                                <div class="progress-bar xp-bar" style="width: <?php echo $level_progress; ?>%">
                                    <span class="progress-percentage"><?php echo round($level_progress); ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="col-md-6">
                        <div class="quick-stats">
                            <div class="stat-item">
                                <div class="stat-icon points-icon">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-number"><?php echo number_format($child_data['total_points']); ?></div>
                                    <div class="stat-label">Points</div>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon streak-icon">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-number"><?php echo $login_streak; ?></div>
                                    <div class="stat-label">Day Streak</div>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon lessons-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-number"><?php echo $lessons_today; ?></div>
                                    <div class="stat-label">Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Play Button -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <a href="play.php" class="btn-play-main" onclick="playClickSound()">
                <div class="btn-play-content">
                    <i class="fas fa-play"></i>
                    <span>PLAY NOW</span>
                </div>
                <div class="btn-play-pulse"></div>
            </a>
            <p class="play-subtitle">Start your learning adventure!</p>
        </div>
    </div>

    <!-- Learning Courses Grid -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-book"></i>
                    Learning Adventures
                </h2>
                <p class="section-subtitle">Choose your favorite subject and start learning!</p>
            </div>
        </div>
    </div>
    
    <div class="row courses-grid mb-4">
        <?php foreach ($courses as $course): ?>
        <?php 
        $course_progress = getCourseProgress($child_data['id'], $course['id']);
        $course_icon = getCourseIcon($course['category']);
        $course_color = getCourseColor($course['category']);
        ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="course-card" onclick="selectCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')">
                <div class="course-header">
                    <div class="course-icon-bg" style="background-color: <?php echo $course_color; ?>">
                        <i class="<?php echo $course_icon; ?>"></i>
                    </div>
                    <div class="course-difficulty">
                        <span class="difficulty-badge difficulty-<?php echo $course['difficulty_level']; ?>">
                            <?php echo ucfirst($course['difficulty_level']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="course-body">
                    <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                    
                    <div class="course-progress">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="progress-label">Progress</span>
                            <span class="progress-percentage"><?php echo $course_progress; ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar course-progress-bar" style="width: <?php echo $course_progress; ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="course-footer">
                    <div class="course-stats">
                        <span class="course-stat">
                            <i class="fas fa-bolt text-warning"></i>
                            <?php echo getTotalCourseXP($course['id']); ?> XP
                        </span>
                        <span class="course-stat">
                            <i class="fas fa-coins text-warning"></i>
                            <?php echo getTotalCoursePoints($course['id']); ?> pts
                        </span>
                    </div>
                    <div class="course-action">
                        <button class="btn-course-action">
                            <?php echo $course_progress > 0 ? 'Continue' : 'Start'; ?>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Daily Quests Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-scroll"></i>
                    Daily Quests
                </h2>
                <p class="section-subtitle">Complete challenges to earn extra rewards!</p>
            </div>
        </div>
    </div>
    
    <div class="row quests-section mb-4">
        <?php foreach ($daily_quests as $quest): ?>
        <?php 
        $quest_progress = min(100, ($quest['progress_value'] / $quest['requirement_value']) * 100);
        $quest_status = $quest['status'] ?? 'not_started';
        $is_completed = $quest_status === 'completed';
        ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="quest-card <?php echo $is_completed ? 'completed' : ''; ?>">
                <div class="quest-icon">
                    <?php if ($is_completed): ?>
                    <i class="fas fa-check-circle text-success"></i>
                    <?php else: ?>
                    <i class="fas fa-<?php echo getQuestIcon($quest['requirement_type']); ?>"></i>
                    <?php endif; ?>
                </div>
                
                <div class="quest-content">
                    <h4 class="quest-title"><?php echo htmlspecialchars($quest['title']); ?></h4>
                    <p class="quest-description"><?php echo htmlspecialchars($quest['description']); ?></p>
                    
                    <div class="quest-progress">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="quest-progress-text">
                                <?php echo $quest['progress_value']; ?> / <?php echo $quest['requirement_value']; ?>
                            </span>
                            <span class="quest-progress-percentage"><?php echo round($quest_progress); ?>%</span>
                        </div>
                        <div class="progress quest-progress-bar">
                            <div class="progress-bar <?php echo $is_completed ? 'bg-success' : 'bg-primary'; ?>" 
                                 style="width: <?php echo $quest_progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="quest-rewards">
                        <span class="reward-badge">
                            <i class="fas fa-coins"></i> <?php echo $quest['points_reward']; ?>
                        </span>
                        <span class="reward-badge">
                            <i class="fas fa-bolt"></i> <?php echo $quest['xp_reward']; ?> XP
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Achievements -->
    <?php if ($recent_achievements): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-trophy"></i>
                    Recent Achievements
                </h2>
                <p class="section-subtitle">Look what you've accomplished! 🎉</p>
            </div>
        </div>
    </div>
    
    <div class="row achievements-section mb-4">
        <div class="col-12">
            <div class="achievements-carousel">
                <div class="row">
                    <?php foreach ($recent_achievements as $achievement): ?>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="achievement-card">
                            <div class="achievement-icon">
                                <img src="<?php echo SITE_URL; ?>/assets/images/badges/<?php echo htmlspecialchars($achievement['badge_icon']); ?>" 
                                     alt="<?php echo htmlspecialchars($achievement['title']); ?>" 
                                     class="achievement-badge">
                            </div>
                            <div class="achievement-info">
                                <h5 class="achievement-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                <p class="achievement-description"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                <small class="achievement-date">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo timeAgo($achievement['earned_at']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="quick-actions">
                <div class="row">
                    <div class="col-6 col-md-3 mb-3">
                        <a href="profile.php" class="action-card">
                            <div class="action-icon bg-purple">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="action-label">My Profile</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="store.php" class="action-card">
                            <div class="action-icon bg-orange">
                                <i class="fas fa-store"></i>
                            </div>
                            <span class="action-label">Store</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="#" class="action-card" onclick="showLeaderboard()">
                            <div class="action-icon bg-blue">
                                <i class="fas fa-crown"></i>
                            </div>
                            <span class="action-label">Leaderboard</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="#" class="action-card" onclick="showHelp()">
                            <div class="action-icon bg-green">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <span class="action-label">Help</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Course Selection Form -->
<form id="courseSelectionForm" method="POST" action="play.php" style="display: none;">
    <input type="hidden" name="selected_course_id" id="selectedCourseId">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
</form>

<script>
function selectCourse(courseId, courseTitle) {
    // Play sound effect
    playClickSound();
    
    // Show loading animation
    showLoading();
    
    // Set course and submit form
    document.getElementById('selectedCourseId').value = courseId;
    
    setTimeout(() => {
        document.getElementById('courseSelectionForm').submit();
    }, 500);
}

function playClickSound() {
    const clickSound = document.getElementById('clickSound');
    if (clickSound) {
        clickSound.currentTime = 0;
        clickSound.play().catch(e => console.log('Audio play failed:', e));
    }
}

function showLoading() {
    // Create loading overlay
    const loading = document.createElement('div');
    loading.className = 'loading-overlay';
    loading.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>';
    document.body.appendChild(loading);
}

function showLeaderboard() {
    playClickSound();
    alert('Leaderboard coming soon! 🏆');
}

function showHelp() {
    playClickSound();
    alert('Need help? Ask your parent for assistance! 🤗');
}

// Add interactive animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars on load
    setTimeout(() => {
        document.querySelectorAll('.progress-bar').forEach(bar => {
            bar.style.transition = 'width 1s ease-in-out';
        });
    }, 100);
    
    // Add hover effects to cards
    document.querySelectorAll('.course-card, .quest-card, .action-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<style>
/* Additional inline styles for immediate effect */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    margin-bottom: 2rem;
}

.animated-title {
    font-size: 2.5rem;
    font-weight: 700;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.btn-play-main {
    position: relative;
    display: inline-block;
    background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
    color: white;
    font-size: 2rem;
    font-weight: 700;
    padding: 2rem 4rem;
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3);
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3); }
    50% { box-shadow: 0 15px 40px rgba(245, 87, 108, 0.5); }
    100% { box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3); }
}

.btn-play-main:hover {
    transform: scale(1.05);
    color: white;
    text-decoration: none;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    font-size: 2rem;
    color: #667eea;
}
</style>

<?php
// Helper functions
function getCourseIcon($category) {
    $icons = [
        'coding' => 'fas fa-code',
        'business' => 'fas fa-briefcase',
        'story' => 'fas fa-book',
        'math' => 'fas fa-calculator',
        'science' => 'fas fa-flask'
    ];
    
    return $icons[$category] ?? 'fas fa-book';
}

function getCourseColor($category) {
    $colors = [
        'coding' => '#667eea',
        'business' => '#f093fb',
        'story' => '#feca57',
        'math' => '#48dbfb',
        'science' => '#ff6b6b'
    ];
    
    return $colors[$category] ?? '#667eea';
}

function getTotalCourseXP($course_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(xp_reward) as total FROM lessons WHERE course_id = ? AND is_active = 1");
    $stmt->execute([$course_id]);
    return $stmt->fetch()['total'] ?? 0;
}

function getTotalCoursePoints($course_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(points_reward) as total FROM lessons WHERE course_id = ? AND is_active = 1");
    $stmt->execute([$course_id]);
    return $stmt->fetch()['total'] ?? 0;
}

function getQuestIcon($requirement_type) {
    $icons = [
        'lessons_complete' => 'book-open',
        'points_earn' => 'coins',
        'time_spent' => 'clock',
        'streak' => 'fire'
    ];
    
    return 'fas fa-' . ($icons[$requirement_type] ?? 'star');
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

include '../includes/footer.php';
?>