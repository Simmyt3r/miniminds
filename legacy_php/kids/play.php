<?php
/**
 * MiniMinds Academy - Kids Play Page
 * Interactive lesson interface for children
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Protect page - only children can access
checkLogin('child');

$page_title = 'Play & Learn - MiniMinds Academy';

// Get child's current data
$child_data = getCurrentUser();
$selected_course_id = $_POST['selected_course_id'] ?? $_GET['course'] ?? null;

// Get available courses
$courses = getAvailableCourses($child_data['age']);

// Get course details if selected
$selected_course = null;
$lessons = [];
if ($selected_course_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = ? AND is_active = 1");
    $stmt->execute([$selected_course_id]);
    $selected_course = $stmt->fetch();
    
    if ($selected_course) {
        // Get lessons for this course
        $stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = ? AND is_active = 1 ORDER BY order_in_course");
        $stmt->execute([$selected_course_id]);
        $lessons = $stmt->fetchAll();
        
        // Get progress for each lesson
        foreach ($lessons as &$lesson) {
            $lesson['progress'] = getLessonProgress($child_data['id'], $lesson['id']);
        }
    }
}

include '../includes/header.php';
?>

<div class="kids-play-page">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="play-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="play-title">
                                <span class="bounce-emoji">🎮</span>
                                Play & Learn
                                <span class="bounce-emoji">🌟</span>
                            </h1>
                            <p class="play-subtitle">Choose your learning adventure!</p>
                        </div>
                        <div class="player-stats">
                            <div class="stat-item">
                                <i class="fas fa-star text-warning"></i>
                                <span>Level <?php echo $child_data['current_level']; ?></span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-bolt text-primary"></i>
                                <span><?php echo $child_data['current_xp']; ?> XP</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-coins text-warning"></i>
                                <span><?php echo number_format($child_data['total_points']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$selected_course): ?>
        <!-- Course Selection -->
        <div class="row">
            <div class="col-12">
                <div class="selection-prompt text-center mb-4">
                    <h2 class="selection-title">Pick Your Adventure! 🎯</h2>
                    <p>What would you like to learn today?</p>
                </div>
            </div>
        </div>

        <div class="row courses-selection-grid">
            <?php foreach ($courses as $course): ?>
            <?php 
            $course_progress = getCourseProgress($child_data['id'], $course['id']);
            $course_icon = getCourseIcon($course['category']);
            $course_color = getCourseColor($course['category']);
            $total_lessons = count(array_filter($lessons ?? [], function($l) use ($course) { return $l['course_id'] == $course['id']; }));
            $completed_lessons = getCompletedLessonsCount($child_data['id'], $course['id']);
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="course-selection-card" onclick="selectCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')">
                    <div class="course-selection-header">
                        <div class="course-icon-large" style="background: <?php echo $course_color; ?>">
                            <i class="<?php echo $course_icon; ?>"></i>
                        </div>
                        <div class="course-selection-badge">
                            <span class="difficulty-badge difficulty-<?php echo $course['difficulty_level']; ?>">
                                <?php echo ucfirst($course['difficulty_level']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="course-selection-body">
                        <h3 class="course-selection-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="course-selection-description"><?php echo htmlspecialchars($course['description']); ?></p>
                        
                        <div class="course-stats-summary">
                            <div class="stat-summary">
                                <i class="fas fa-book-open me-1"></i>
                                <?php echo $total_lessons; ?> Lessons
                            </div>
                            <div class="stat-summary">
                                <i class="fas fa-check-circle me-1"></i>
                                <?php echo $completed_lessons; ?> Completed
                            </div>
                        </div>
                        
                        <div class="course-progress-summary mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Progress</span>
                                <span><?php echo $course_progress; ?>%</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-gradient" style="width: <?php echo $course_progress; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-selection-footer">
                        <button class="btn-play-course">
                            <?php echo $course_progress > 0 ? 'Continue Learning' : 'Start Adventure' ?>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <!-- Lesson Selection -->
        <div class="row">
            <div class="col-12">
                <div class="lesson-header">
                    <button class="btn-back" onclick="goBackToCourses()">
                        <i class="fas fa-arrow-left"></i>
                        Back to Courses
                    </button>
                    <div class="course-info">
                        <div class="course-icon-header" style="background: <?php echo getCourseColor($selected_course['category']); ?>">
                            <i class="<?php echo getCourseIcon($selected_course['category']); ?>"></i>
                        </div>
                        <div>
                            <h2 class="course-title-large"><?php echo htmlspecialchars($selected_course['title']); ?></h2>
                            <p class="course-subtitle"><?php echo htmlspecialchars($selected_course['description']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row lessons-grid">
            <?php if ($lessons): ?>
                <?php foreach ($lessons as $lesson): ?>
                <?php 
                $is_completed = $lesson['progress']['status'] === 'completed';
                $is_in_progress = $lesson['progress']['status'] === 'in_progress';
                $is_locked = false;
                
                // Check if lesson is locked (previous lesson not completed)
                if ($lesson['order_in_course'] > 1) {
                    $prev_lesson_order = $lesson['order_in_course'] - 1;
                    $prev_completed = false;
                    foreach ($lessons as $prev_lesson) {
                        if ($prev_lesson['order_in_course'] === $prev_lesson_order && $prev_lesson['progress']['status'] === 'completed') {
                            $prev_completed = true;
                            break;
                        }
                    }
                    $is_locked = !$prev_completed;
                }
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="lesson-card <?php echo $is_completed ? 'completed' : ''; ?> <?php echo $is_locked ? 'locked' : ''; ?>" 
                         onclick="<?php echo $is_locked ? 'showLockedLesson()' : 'startLesson(' . $lesson['id'] . ', \'' . htmlspecialchars($lesson['title']) . '\', \'' . $lesson['lesson_type'] . '\')' ?>">
                        
                        <div class="lesson-header-info">
                            <div class="lesson-number">
                                <?php echo $lesson['order_in_course']; ?>
                            </div>
                            <div class="lesson-type-icon">
                                <?php if ($is_locked): ?>
                                <i class="fas fa-lock"></i>
                                <?php elseif ($is_completed): ?>
                                <i class="fas fa-check-circle text-success"></i>
                                <?php elseif ($is_in_progress): ?>
                                <i class="fas fa-play-circle text-warning"></i>
                                <?php else: ?>
                                <i class="fas fa-circle"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="lesson-body">
                            <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                            <p class="lesson-description"><?php echo htmlspecialchars($lesson['content']); ?></p>
                            
                            <div class="lesson-type-badge">
                                <span class="badge bg-<?php echo getLessonTypeColor($lesson['lesson_type']); ?>">
                                    <i class="fas fa-<?php echo getLessonTypeIcon($lesson['lesson_type']); ?> me-1"></i>
                                    <?php echo ucfirst($lesson['lesson_type']); ?>
                                </span>
                            </div>
                            
                            <div class="lesson-progress-info">
                                <?php if ($is_completed): ?>
                                <div class="completed-indicator">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="text-success">Completed!</span>
                                </div>
                                <?php elseif ($is_in_progress): ?>
                                <div class="progress-indicator">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>In Progress</small>
                                        <small><?php echo $lesson['progress']['completion_percentage']; ?>%</small>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" style="width: <?php echo $lesson['progress']['completion_percentage']; ?>%"></div>
                                    </div>
                                </div>
                                <?php elseif ($is_locked): ?>
                                <div class="locked-indicator">
                                    <i class="fas fa-lock text-muted me-2"></i>
                                    <span class="text-muted">Complete previous lesson</span>
                                </div>
                                <?php else: ?>
                                <div class="available-indicator">
                                    <i class="fas fa-play-circle text-primary me-2"></i>
                                    <span class="text-primary">Ready to start!</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="lesson-footer">
                            <div class="lesson-rewards">
                                <span class="reward-badge">
                                    <i class="fas fa-bolt text-warning"></i>
                                    <?php echo $lesson['xp_reward']; ?> XP
                                </span>
                                <span class="reward-badge">
                                    <i class="fas fa-coins text-warning"></i>
                                    <?php echo $lesson['points_reward']; ?>
                                </span>
                            </div>
                            
                            <div class="lesson-action">
                                <?php if ($is_locked): ?>
                                <button class="btn-lesson btn-locked" disabled>
                                    <i class="fas fa-lock me-2"></i>Locked
                                </button>
                                <?php elseif ($is_completed): ?>
                                <button class="btn-lesson btn-replay">
                                    <i class="fas fa-redo me-2"></i>Replay
                                </button>
                                <?php elseif ($is_in_progress): ?>
                                <button class="btn-lesson btn-continue">
                                    <i class="fas fa-play me-2"></i>Continue
                                </button>
                                <?php else: ?>
                                <button class="btn-lesson btn-start">
                                    <i class="fas fa-play me-2"></i>Start
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="col-12">
                <div class="no-lessons-message text-center">
                    <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                    <h3>No Lessons Available</h3>
                    <p class="text-muted">This course doesn't have any lessons yet.</p>
                    <button class="btn btn-primary" onclick="goBackToCourses()">
                        <i class="fas fa-arrow-left me-2"></i>Back to Courses
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Hidden Course Selection Form -->
<form id="courseSelectionForm" method="POST" action="play.php" style="display: none;">
    <input type="hidden" name="selected_course_id" id="selectedCourseId">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
</form>

<!-- Hidden Lesson Start Form -->
<form id="lessonStartForm" method="POST" action="lesson.php" style="display: none;">
    <input type="hidden" name="lesson_id" id="selectedLessonId">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
</form>

<script>
// Course selection
function selectCourse(courseId, courseTitle) {
    // Play sound effect
    if (window.MiniMindsKids) {
        window.MiniMindsKids.playSound('click');
    }
    
    // Show loading animation
    showLoading();
    
    // Set course and submit form
    document.getElementById('selectedCourseId').value = courseId;
    
    setTimeout(() => {
        document.getElementById('courseSelectionForm').submit();
    }, 500);
}

// Start lesson
function startLesson(lessonId, lessonTitle, lessonType) {
    // Play sound effect
    if (window.MiniMindsKids) {
        window.MiniMindsKids.playSound('click');
    }
    
    // Show loading animation
    showLoading();
    
    // Set lesson and submit form
    document.getElementById('selectedLessonId').value = lessonId;
    
    setTimeout(() => {
        document.getElementById('lessonStartForm').submit();
    }, 500);
}

// Show locked lesson message
function showLockedLesson() {
    if (window.MiniMindsKids) {
        window.MiniMindsKids.playSound('click');
    }
    
    MiniMinds.showNotification('Complete the previous lesson to unlock this one! 📚', 'info');
}

// Go back to courses
function goBackToCourses() {
    if (window.MiniMindsKids) {
        window.MiniMindsKids.playSound('click');
    }
    
    window.location.href = 'play.php';
}

// Show loading animation
function showLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading-overlay';
    loading.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>';
    document.body.appendChild(loading);
}

// Initialize animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate course cards on load
    const cards = document.querySelectorAll('.course-selection-card, .lesson-card');
    cards.forEach((card, index) => {
        card.style.animation = `slide-in-up ${0.5 + index * 0.1}s ease-out`;
    });
    
    // Add hover effects
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (window.MiniMindsKids) {
                window.MiniMindsKids.createMagicalParticles(this);
            }
        });
    });
    
    // Play welcome sound
    if (window.MiniMindsKids) {
        setTimeout(() => {
            window.MiniMindsKids.playSound('welcome');
        }, 500);
    }
});

// Add slide-in animation
if (!document.querySelector('#slide-in-animation')) {
    const style = document.createElement('style');
    style.id = 'slide-in-animation';
    style.textContent = `
        @keyframes slide-in-up {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
}
</script>

<style>
.kids-play-page {
    min-height: 100vh;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem 0;
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
    animation: float-shape 8s ease-in-out infinite;
}

.shape-1 { width: 100px; height: 100px; top: 10%; left: 5%; animation-delay: 0s; }
.shape-2 { width: 150px; height: 150px; top: 60%; right: 10%; animation-delay: 2s; }
.shape-3 { width: 80px; height: 80px; top: 30%; right: 30%; animation-delay: 4s; }
.shape-4 { width: 120px; height: 120px; bottom: 20%; left: 15%; animation-delay: 1s; }

@keyframes float-shape {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.play-header {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 25px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.play-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.bounce-emoji {
    display: inline-block;
    animation: bounce 2s ease-in-out infinite;
}

.bounce-emoji:nth-child(2) {
    animation-delay: 1s;
}

.play-subtitle {
    font-size: 1.2rem;
    color: #6c757d;
    margin: 0;
}

.player-stats {
    display: flex;
    gap: 1.5rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.9);
    padding: 0.75rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.selection-prompt {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 25px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.selection-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.course-selection-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.course-selection-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.course-selection-header {
    position: relative;
    padding: 2rem 2rem 1rem;
    text-align: center;
}

.course-icon-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    margin: 0 auto 1rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.course-selection-body {
    padding: 1rem 2rem;
}

.course-selection-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
    text-align: center;
}

.course-selection-description {
    color: #6c757d;
    text-align: center;
    margin-bottom: 1.5rem;
}

.course-stats-summary {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.stat-summary {
    background: rgba(102, 126, 234, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: 600;
}

.progress-sm {
    height: 8px;
    border-radius: 10px;
}

.bg-gradient {
    background: linear-gradient(90deg, #48dbfb, #0abde3);
}

.course-selection-footer {
    padding: 1.5rem 2rem 2rem;
    text-align: center;
}

.btn-play-course {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-play-course:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.lesson-header {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 25px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.btn-back {
    background: rgba(102, 126, 234, 0.1);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    color: var(--primary-color);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.btn-back:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(-5px);
}

.course-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.course-icon-header {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    flex-shrink: 0;
}

.course-title-large {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.course-subtitle {
    color: #6c757d;
    margin: 0;
}

.lesson-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    backdrop-filter: blur(10px);
    position: relative;
}

.lesson-card:hover:not(.locked) {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.lesson-card.locked {
    opacity: 0.7;
    cursor: not-allowed;
}

.lesson-card.completed {
    border: 2px solid #28a745;
}

.lesson-header-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 1.5rem 0;
}

.lesson-number {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.lesson-type-icon {
    font-size: 1.5rem;
}

.lesson-body {
    padding: 1rem 1.5rem;
}

.lesson-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.lesson-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.lesson-type-badge {
    margin-bottom: 1rem;
}

.lesson-progress-info {
    text-align: center;
    padding: 1rem;
    background: rgba(248, 249, 250, 0.8);
    border-radius: 15px;
    margin-bottom: 1rem;
}

.lesson-footer {
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.lesson-rewards {
    display: flex;
    gap: 0.5rem;
}

.reward-badge {
    background: linear-gradient(135deg, #feca57, #ff9ff3);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
}

.btn-lesson {
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-lesson.btn-start {
    background: linear-gradient(135deg, #48dbfb, #0abde3);
    color: white;
}

.btn-lesson.btn-continue {
    background: linear-gradient(135deg, #feca57, #ff9ff3);
    color: white;
}

.btn-lesson.btn-replay {
    background: linear-gradient(135deg, #48bb78, #38a169);
    color: white;
}

.btn-lesson.btn-locked {
    background: #e9ecef;
    color: #6c757d;
}

.no-lessons-message {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 25px;
    padding: 3rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(102, 126, 234, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    background: white;
    padding: 3rem;
    border-radius: 25px;
    font-size: 2.5rem;
    color: var(--primary-color);
    text-align: center;
}

@media (max-width: 768px) {
    .play-title {
        font-size: 2rem;
    }
    
    .player-stats {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .stat-item {
        justify-content: center;
    }
    
    .course-info {
        flex-direction: column;
        text-align: center;
    }
    
    .course-icon-header {
        margin: 0 auto;
    }
    
    .lesson-card {
        margin-bottom: 1rem;
    }
    
    .lesson-footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<?php
// Helper functions
function getLessonProgress($child_id, $lesson_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM progress WHERE child_id = ? AND lesson_id = ?");
    $stmt->execute([$child_id, $lesson_id]);
    $progress = $stmt->fetch();
    
    if (!$progress) {
        return ['status' => 'not_started', 'completion_percentage' => 0];
    }
    
    return $progress;
}

function getCompletedLessonsCount($child_id, $course_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM progress p 
        JOIN lessons l ON p.lesson_id = l.id 
        WHERE p.child_id = ? AND l.course_id = ? AND p.status = 'completed'
    ");
    $stmt->execute([$child_id, $course_id]);
    return $stmt->fetch()['count'];
}

function getLessonTypeColor($type) {
    $colors = [
        'story' => 'info',
        'game' => 'success',
        'quiz' => 'warning',
        'video' => 'primary',
        'interactive' => 'danger'
    ];
    
    return $colors[$type] ?? 'secondary';
}

function getLessonTypeIcon($type) {
    $icons = [
        'story' => 'book-open',
        'game' => 'gamepad',
        'quiz' => 'question-circle',
        'video' => 'video',
        'interactive' => 'hand-pointer'
    ];
    
    return $icons[$type] ?? 'circle';
}

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

include '../includes/footer.php';
?>