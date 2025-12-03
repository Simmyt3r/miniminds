-- MiniMinds Academy Database Schema
-- PHP Gamified Learning Platform for Children (4-9)

CREATE DATABASE IF NOT EXISTS miniminds_academy;
USE miniminds_academy;

-- Users table (Parents and Admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('parent', 'admin') DEFAULT 'parent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    phone VARCHAR(20),
    subscription_plan ENUM('free', 'premium', 'family') DEFAULT 'free',
    subscription_expiry DATE NULL
);

-- Children profiles linked to parents
CREATE TABLE children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(50) NOT NULL,
    pin_code VARCHAR(4) NOT NULL,
    age INT NOT NULL,
    avatar_url VARCHAR(255) DEFAULT 'default_avatar.png',
    total_points INT DEFAULT 0,
    current_level INT DEFAULT 1,
    current_xp INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_active TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    CHECK (age BETWEEN 4 AND 9)
);

-- Course categories
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('coding', 'business', 'story', 'math', 'science') NOT NULL,
    thumbnail_url VARCHAR(255),
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    min_age INT DEFAULT 4,
    max_age INT DEFAULT 9,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sort_order INT DEFAULT 0
);

-- Individual lessons within courses
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    lesson_type ENUM('story', 'game', 'quiz', 'video', 'interactive') NOT NULL,
    xp_reward INT DEFAULT 10,
    points_reward INT DEFAULT 5,
    order_in_course INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Progress tracking for children
CREATE TABLE progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    lesson_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    completion_percentage INT DEFAULT 0,
    xp_earned INT DEFAULT 0,
    points_earned INT DEFAULT 0,
    time_spent_minutes INT DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_child_lesson (child_id, lesson_id)
);

-- Achievements and badges
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    badge_icon VARCHAR(255),
    points_reward INT DEFAULT 0,
    xp_reward INT DEFAULT 0,
    achievement_type ENUM('completion', 'streak', 'milestone', 'special') NOT NULL,
    requirement_value INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Child achievements (earned badges)
CREATE TABLE child_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    achievement_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_child_achievement (child_id, achievement_id)
);

-- Virtual store items
CREATE TABLE store_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    item_type ENUM('avatar', 'pet', 'decoration', 'background') NOT NULL,
    price_points INT NOT NULL,
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Child purchases from virtual store
CREATE TABLE child_purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    item_id INT NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_equipped BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES store_items(id) ON DELETE CASCADE
);

-- Daily quests and challenges
CREATE TABLE quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    quest_type ENUM('daily', 'weekly', 'special') NOT NULL,
    requirement_type ENUM('lessons_complete', 'points_earn', 'time_spent', 'streak') NOT NULL,
    requirement_value INT NOT NULL,
    points_reward INT NOT NULL,
    xp_reward INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE,
    end_date DATE
);

-- Child quest progress
CREATE TABLE child_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    quest_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed', 'expired') DEFAULT 'not_started',
    progress_value INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    UNIQUE KEY unique_child_quest (child_id, quest_id)
);

-- Login sessions
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    child_id INT,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE
);

-- System settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample courses
INSERT INTO courses (title, description, category, thumbnail_url, difficulty_level, min_age, max_age) VALUES
('Coding Adventures', 'Learn programming basics through fun interactive stories and games', 'coding', 'coding_thumbnail.png', 'beginner', 4, 6),
('Business Buddies', 'Introduction to entrepreneurship and money management for kids', 'business', 'business_thumbnail.png', 'beginner', 6, 9),
('Story Magic', 'Engaging stories that teach valuable life lessons and reading skills', 'story', 'story_thumbnail.png', 'beginner', 4, 9);

-- Insert sample lessons for Coding Adventures
INSERT INTO lessons (course_id, title, content, lesson_type, xp_reward, points_reward, order_in_course) VALUES
(1, 'Hello Computer!', 'Meet your new friend - the computer! Learn what makes it special.', 'story', 10, 5, 1),
(1, 'Colors and Commands', 'Mix colors like a digital artist while learning basic commands', 'game', 15, 8, 2),
(1, 'The Loop Detective', 'Help Detective Loop solve puzzles by finding repeating patterns', 'interactive', 20, 10, 3),
(1, 'If-Then Adventure', 'Make choices in our magical story and see how computers make decisions', 'story', 15, 8, 4),
(1, 'Code Your First Robot', 'Program a virtual robot to dance and collect stars!', 'game', 25, 12, 5);

-- Insert sample lessons for Business Buddies
INSERT INTO lessons (course_id, title, content, lesson_type, xp_reward, points_reward, order_in_course) VALUES
(2, 'Money Matters', 'Learn where money comes from and why it\'s important', 'story', 10, 5, 1),
(2, 'The Lemonade Stand', 'Run your first virtual business and learn about profit', 'game', 20, 10, 2),
(2, 'Saving Secrets', 'Help Penny Pig learn the magic of saving money', 'interactive', 15, 8, 3),
(2, 'Shop Till You Drop', 'Make smart choices while shopping with virtual money', 'game', 18, 9, 4);

-- Insert sample lessons for Story Magic
INSERT INTO lessons (course_id, title, content, lesson_type, xp_reward, points_reward, order_in_course) VALUES
(3, 'The Brave Little Robot', 'A story about courage and helping others', 'story', 10, 5, 1),
(3, 'Dragon\'s Math Adventure', 'Help the dragon solve math problems to save the kingdom', 'interactive', 15, 8, 2),
(3, 'The Friendship Garden', 'Learn about friendship while growing a magical garden', 'game', 12, 6, 3),
(3, 'Space Explorer\'s Journey', 'Explore planets and learn fun facts about space', 'story', 10, 5, 4);

-- Insert sample achievements
INSERT INTO achievements (title, description, badge_icon, points_reward, xp_reward, achievement_type, requirement_value) VALUES
('First Steps', 'Complete your first lesson', 'badge_first_steps.png', 10, 20, 'completion', 1),
('Quick Learner', 'Complete 5 lessons', 'badge_quick_learner.png', 25, 50, 'completion', 5),
('Star Student', 'Complete 10 lessons', 'badge_star_student.png', 50, 100, 'completion', 10),
('Point Collector', 'Earn 100 points', 'badge_point_collector.png', 20, 40, 'milestone', 100),
('XP Master', 'Earn 200 XP', 'badge_xp_master.png', 30, 60, 'milestone', 200),
('Week Warrior', 'Log in 7 days in a row', 'badge_week_warrior.png', 40, 80, 'streak', 7);

-- Insert sample store items
INSERT INTO store_items (name, description, item_type, price_points, image_url) VALUES
('Cool Dinosaur Avatar', 'Roar like a dinosaur with this awesome avatar!', 'avatar', 50, 'avatar_dino.png'),
('Space Explorer Outfit', 'Ready for space adventures with this cool outfit', 'avatar', 75, 'avatar_space.png'),
('Magic Unicorn Pet', 'A magical unicorn friend to accompany you', 'pet', 100, 'pet_unicorn.png'),
('Robot Companion', 'A helpful robot that loves learning', 'pet', 80, 'pet_robot.png'),
('Rainbow Background', 'Colorful rainbow background for your profile', 'background', 30, 'bg_rainbow.png'),
('Stars and Moon Theme', 'Beautiful night sky with twinkling stars', 'background', 40, 'bg_stars.png');

-- Insert sample quests
INSERT INTO quests (title, description, quest_type, requirement_type, requirement_value, points_reward, xp_reward, start_date, end_date) VALUES
('Daily Lesson Hero', 'Complete 2 lessons today', 'daily', 'lessons_complete', 2, 20, 40, CURDATE(), CURDATE()),
('Point Collector Daily', 'Earn 25 points today', 'daily', 'points_earn', 25, 15, 30, CURDATE(), CURDATE()),
('Weekly Champion', 'Complete 10 lessons this week', 'weekly', 'lessons_complete', 10, 100, 200, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY)),
(' Learning Streak', 'Log in 3 days in a row', 'daily', 'streak', 3, 30, 60, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY));

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@miniminds.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert sample parent user (password: parent123)
INSERT INTO users (username, email, password_hash, full_name, role, subscription_plan) VALUES
('testparent', 'parent@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Parent', 'parent', 'premium');

-- Insert sample children
INSERT INTO children (parent_id, username, display_name, pin_code, age, avatar_url, total_points, current_level, current_xp) VALUES
(2, 'kid1', 'Lucky Kid', '1234', 6, 'avatar_default.png', 150, 2, 75),
(2, 'kid2', 'Smart Kid', '5678', 8, 'avatar_default.png', 200, 3, 150);

-- Insert system settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'MiniMinds Academy', 'The name of the learning platform'),
('min_age', '4', 'Minimum age for children'),
('max_age', '9', 'Maximum age for children'),
('points_per_level', '100', 'XP required to level up'),
('max_daily_time', '120', 'Maximum daily usage time in minutes'),
('parent_verification_required', 'true', 'Require parent verification for certain actions');

-- Create indexes for better performance
CREATE INDEX idx_children_parent_id ON children(parent_id);
CREATE INDEX idx_progress_child_id ON progress(child_id);
CREATE INDEX idx_progress_lesson_id ON progress(lesson_id);
CREATE INDEX idx_lessons_course_id ON lessons(course_id);
CREATE INDEX idx_child_achievements_child_id ON child_achievements(child_id);
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_child_id ON sessions(child_id);
CREATE INDEX idx_child_quests_child_id ON child_quests(child_id);