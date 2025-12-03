/**
 * MiniMinds Academy - Kids Theme JavaScript
 * Interactive features, sound effects, and gamification for children
 */

// Kids-specific global variables
let soundEnabled = true;
let currentPoints = 0;
let currentXP = 0;
let currentLevel = 1;
let animationsEnabled = true;
let confettiEnabled = true;

// Initialize kids theme
document.addEventListener('DOMContentLoaded', function() {
    initializeKidsTheme();
    setupGamification();
    setupSoundEffects();
    setupInteractiveElements();
    setupProgressAnimations();
    initializeKidsAnimations();
});

/**
 * Initialize kids theme specific features
 */
function initializeKidsTheme() {
    // Get current stats from page
    const pointsElement = document.querySelector('.points-stat');
    const xpElement = document.querySelector('.xp-stat');
    const levelElement = document.querySelector('.level-stat');
    
    if (pointsElement) currentPoints = parseInt(pointsElement.textContent.replace(/,/g, '')) || 0;
    if (xpElement) currentXP = parseInt(xpElement.textContent) || 0;
    if (levelElement) currentLevel = parseInt(levelElement.textContent) || 1;
    
    // Load preferences
    loadKidsPreferences();
    
    // Setup floating animations
    setupFloatingElements();
    
    // Setup rainbow cursor effect
    setupRainbowCursor();
    
    // Setup surprise animations
    setupSurpriseAnimations();
}

/**
 * Setup gamification features
 */
function setupGamification() {
    // Animate numbers on load
    animateValue('.stats-number', 0, currentPoints, 2000);
    animateValue('.xp-stat', 0, currentXP, 2000);
    animateValue('.level-stat', 0, currentLevel, 1000);
    
    // Setup achievement notifications
    setupAchievementPopups();
    
    // Setup level up celebrations
    setupLevelUpCelebration();
    
    // Setup quest completion effects
    setupQuestEffects();
    
    // Setup streak counter
    setupStreakCounter();
}

/**
 * Setup sound effects
 */
function setupSoundEffects() {
    // Create audio context for sound effects
    window.AudioContext = window.AudioContext || window.webkitAudioContext;
    
    // Preload sounds
    preloadSounds();
    
    // Add sound toggle button
    addSoundToggleButton();
    
    // Play welcome sound
    setTimeout(() => {
        playSound('welcome');
    }, 500);
}

/**
 * Preload sound files
 */
function preloadSounds() {
    const sounds = ['click', 'success', 'levelup', 'achievement', 'quest', 'welcome', 'hover'];
    
    sounds.forEach(soundName => {
        const audio = new Audio(`/assets/sounds/${soundName}.mp3`);
        audio.preload = 'auto';
        audio.volume = 0.5;
        window[`sound_${soundName}`] = audio;
    });
}

/**
 * Play sound effect
 */
function playSound(soundName) {
    if (!soundEnabled) return;
    
    const audio = window[`sound_${soundName}`];
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(e => {
            console.log('Audio play failed:', e);
        });
    }
}

/**
 * Add sound toggle button
 */
function addSoundToggleButton() {
    const soundBtn = document.createElement('button');
    soundBtn.className = 'btn sound-toggle-btn';
    soundBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
    soundBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    `;
    
    soundBtn.addEventListener('click', function() {
        soundEnabled = !soundEnabled;
        this.innerHTML = soundEnabled ? '<i class="fas fa-volume-up"></i>' : '<i class="fas fa-volume-mute"></i>';
        
        if (soundEnabled) {
            playSound('click');
        }
        
        saveKidsPreference('soundEnabled', soundEnabled);
    });
    
    soundBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });
    
    soundBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
    
    document.body.appendChild(soundBtn);
}

/**
 * Setup interactive elements
 */
function setupInteractiveElements() {
    // Add click sounds to buttons
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('button') || e.target.closest('a')) {
            playSound('click');
        }
    });
    
    // Add hover effects
    document.addEventListener('mouseover', function(e) {
        if (e.target.classList.contains('course-card') || 
            e.target.classList.contains('quest-card') ||
            e.target.classList.contains('action-card')) {
            playSound('hover');
        }
    });
    
    // Setup draggable elements (for fun interactions)
    setupDraggableElements();
    
    // Setup magical hover effects
    setupMagicalHover();
}

/**
 * Setup draggable elements for fun interactions
 */
function setupDraggableElements() {
    const draggableElements = document.querySelectorAll('.draggable');
    
    draggableElements.forEach(element => {
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;
        
        element.style.cursor = 'grab';
        element.style.position = 'relative';
        element.style.zIndex = '100';
        
        element.addEventListener('mousedown', dragStart);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', dragEnd);
        
        function dragStart(e) {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
            
            if (e.target === element) {
                isDragging = true;
                element.style.cursor = 'grabbing';
                element.style.zIndex = '1000';
            }
        }
        
        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
                
                xOffset = currentX;
                yOffset = currentY;
                
                element.style.transform = `translate(${currentX}px, ${currentY}px) rotate(${currentX * 0.1}deg)`;
            }
        }
        
        function dragEnd(e) {
            initialX = currentX;
            initialY = currentY;
            
            isDragging = false;
            element.style.cursor = 'grab';
            element.style.zIndex = '100';
            
            // Spring back animation
            element.style.transition = 'transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            element.style.transform = 'translate(0, 0) rotate(0)';
            
            setTimeout(() => {
                element.style.transition = '';
                xOffset = 0;
                yOffset = 0;
            }, 500);
        }
    });
}

/**
 * Setup magical hover effects
 */
function setupMagicalHover() {
    const magicalElements = document.querySelectorAll('.course-card, .quest-card, .btn-play-main');
    
    magicalElements.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            createMagicalParticles(e.target);
        });
    });
}

/**
 * Create magical particles on hover
 */
function createMagicalParticles(element) {
    if (!animationsEnabled) return;
    
    const colors = ['#feca57', '#ff9ff3', '#48dbfb', '#ff6b6b'];
    const particleCount = 5;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute;
            width: 8px;
            height: 8px;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            border-radius: 50%;
            pointer-events: none;
            z-index: 999;
            animation: float-particle 1s ease-out forwards;
        `;
        
        const rect = element.getBoundingClientRect();
        particle.style.left = (rect.left + rect.width / 2 + (Math.random() - 0.5) * rect.width) + 'px';
        particle.style.top = (rect.top + rect.height / 2 + (Math.random() - 0.5) * rect.height) + 'px';
        
        document.body.appendChild(particle);
        
        setTimeout(() => particle.remove(), 1000);
    }
    
    // Add floating particle animation
    if (!document.querySelector('#float-particle-style')) {
        const style = document.createElement('style');
        style.id = 'float-particle-style';
        style.textContent = `
            @keyframes float-particle {
                0% {
                    transform: translate(0, 0) scale(1);
                    opacity: 1;
                }
                100% {
                    transform: translate(${(Math.random() - 0.5) * 100}px, -50px) scale(0);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Setup progress animations
 */
function setupProgressAnimations() {
    // Animate progress bars on scroll
    const progressBars = document.querySelectorAll('.progress-bar');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bar = entry.target;
                const width = bar.style.width || bar.getAttribute('aria-valuenow');
                
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                    bar.style.transition = 'width 1.5s ease-out';
                }, 100);
                
                observer.unobserve(bar);
            }
        });
    });
    
    progressBars.forEach(bar => observer.observe(bar));
    
    // Setup XP bar animation
    animateXPBar();
}

/**
 * Animate XP bar with special effects
 */
function animateXPBar() {
    const xpBar = document.querySelector('.xp-bar');
    if (!xpBar) return;
    
    // Add shimmer effect
    xpBar.style.background = `linear-gradient(90deg, #feca57, #ff9ff3, #48dbfb, #ff6b6b, #feca57)`;
    xpBar.style.backgroundSize = '300% 100%';
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shimmer {
            0% { background-position: 0% 50%; }
            100% { background-position: 300% 50%; }
        }
        .xp-bar {
            animation: shimmer 3s linear infinite;
        }
    `;
    document.head.appendChild(style);
}

/**
 * Initialize kids-specific animations
 */
function initializeKidsAnimations() {
    // Bouncy animations for cards
    const cards = document.querySelectorAll('.course-card, .quest-card, .achievement-card');
    
    cards.forEach((card, index) => {
        card.style.animation = `bounce-in ${0.5 + index * 0.1}s ease-out`;
    });
    
    // Wiggle animation on hover
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (animationsEnabled) {
                this.style.animation = 'wiggle 0.5s ease-in-out';
                setTimeout(() => {
                    this.style.animation = '';
                }, 500);
            }
        });
    });
}

/**
 * Setup achievement popups
 */
function setupAchievementPopups() {
    // Check for newly unlocked achievements
    checkNewAchievements();
    
    // Setup celebration effects
    document.addEventListener('achievementUnlocked', function(e) {
        showAchievementPopup(e.detail.achievement);
        playSound('achievement');
        if (confettiEnabled) {
            createConfetti();
        }
    });
}

/**
 * Show achievement popup
 */
function showAchievementPopup(achievement) {
    const popup = document.createElement('div');
    popup.className = 'achievement-popup';
    popup.innerHTML = `
        <div class="achievement-popup-content">
            <div class="achievement-popup-icon">🏆</div>
            <h3>Achievement Unlocked!</h3>
            <h4>${achievement.title}</h4>
            <p>${achievement.description}</p>
            <div class="achievement-popup-rewards">
                <span class="reward-badge">+${achievement.points} points</span>
                <span class="reward-badge">+${achievement.xp} XP</span>
            </div>
        </div>
    `;
    
    popup.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        z-index: 10000;
        text-align: center;
        animation: popup-bounce 0.6s ease-out forwards;
    `;
    
    document.body.appendChild(popup);
    
    setTimeout(() => {
        popup.style.animation = 'popup-bounce-out 0.3s ease-in forwards';
        setTimeout(() => popup.remove(), 300);
    }, 4000);
    
    // Add popup animations
    if (!document.querySelector('#popup-animations')) {
        const style = document.createElement('style');
        style.id = 'popup-animations';
        style.textContent = `
            @keyframes popup-bounce {
                0% { transform: translate(-50%, -50%) scale(0) rotate(0deg); }
                50% { transform: translate(-50%, -50%) scale(1.1) rotate(5deg); }
                100% { transform: translate(-50%, -50%) scale(1) rotate(0deg); }
            }
            @keyframes popup-bounce-out {
                0% { transform: translate(-50%, -50%) scale(1) rotate(0deg); }
                100% { transform: translate(-50%, -50%) scale(0) rotate(-5deg); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Create confetti effect
 */
function createConfetti() {
    if (!confettiEnabled) return;
    
    const colors = ['#feca57', '#ff9ff3', '#48dbfb', '#ff6b6b', '#667eea'];
    const confettiCount = 100;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.style.cssText = `
            position: fixed;
            width: 10px;
            height: 10px;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            pointer-events: none;
            z-index: 9999;
            left: ${Math.random() * 100}%;
            top: -10px;
            animation: confetti-fall ${2 + Math.random() * 2}s linear forwards;
            transform: rotate(${Math.random() * 360}deg);
        `;
        
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 4000);
    }
    
    // Add confetti animation
    if (!document.querySelector('#confetti-animation')) {
        const style = document.createElement('style');
        style.id = 'confetti-animation';
        style.textContent = `
            @keyframes confetti-fall {
                0% {
                    transform: translateY(0) rotate(0deg);
                    opacity: 1;
                }
                100% {
                    transform: translateY(100vh) rotate(720deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Setup level up celebration
 */
function setupLevelUpCelebration() {
    document.addEventListener('levelUp', function(e) {
        const newLevel = e.detail.level;
        
        // Play special sound
        playSound('levelup');
        
        // Create massive celebration
        createLevelUpAnimation(newLevel);
        
        // Show level up message
        showLevelUpMessage(newLevel);
        
        // Create confetti
        createConfetti();
    });
}

/**
 * Create level up animation
 */
function createLevelUpAnimation(newLevel) {
    const levelDisplay = document.querySelector('.level-badge, .level-stat');
    if (levelDisplay) {
        levelDisplay.style.animation = 'level-up-glow 2s ease-in-out';
        
        setTimeout(() => {
            levelDisplay.textContent = `Level ${newLevel}`;
            levelDisplay.style.animation = '';
        }, 1000);
    }
    
    // Add level up animation
    if (!document.querySelector('#level-up-animations')) {
        const style = document.createElement('style');
        style.id = 'level-up-animations';
        style.textContent = `
            @keyframes level-up-glow {
                0%, 100% { transform: scale(1); box-shadow: 0 0 0 rgba(254, 202, 87, 0); }
                50% { transform: scale(1.2); box-shadow: 0 0 30px rgba(254, 202, 87, 0.8); }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Show level up message
 */
function showLevelUpMessage(newLevel) {
    const message = document.createElement('div');
    message.innerHTML = `
        <div class="level-up-message">
            <h1>🎉 LEVEL UP! 🎉</h1>
            <h2>You're now Level ${newLevel}!</h2>
            <p>Amazing job! Keep up the great work!</p>
        </div>
    `;
    
    message.style.cssText = `
        position: fixed;
        top: 20%;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #feca57, #ff9ff3);
        color: white;
        padding: 2rem 3rem;
        border-radius: 25px;
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
        z-index: 10000;
        animation: level-up-slide 0.8s ease-out;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        message.style.animation = 'level-up-slide-out 0.5s ease-in forwards';
        setTimeout(() => message.remove(), 500);
    }, 3000);
    
    // Add level up slide animations
    if (!document.querySelector('#level-up-slide')) {
        const style = document.createElement('style');
        style.id = 'level-up-slide';
        style.textContent = `
            @keyframes level-up-slide {
                0% { transform: translateX(-50%) translateY(-100px); opacity: 0; }
                100% { transform: translateX(-50%) translateY(0); opacity: 1; }
            }
            @keyframes level-up-slide-out {
                0% { transform: translateX(-50%) translateY(0); opacity: 1; }
                100% { transform: translateX(-50%) translateY(-100px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Setup quest effects
 */
function setupQuestEffects() {
    document.addEventListener('questCompleted', function(e) {
        playSound('quest');
        createQuestCompletionEffect(e.detail.quest);
    });
}

/**
 * Create quest completion effect
 */
function createQuestCompletionEffect(quest) {
    const questCard = document.querySelector(`[data-quest-id="${quest.id}"]`);
    if (questCard) {
        questCard.classList.add('quest-completed');
        questCard.style.animation = 'quest-complete 1s ease-in-out';
        
        // Add sparkles
        createSparkles(questCard);
    }
    
    // Add quest completion animation
    if (!document.querySelector('#quest-animations')) {
        const style = document.createElement('style');
        style.id = 'quest-animations';
        style.textContent = `
            @keyframes quest-complete {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); box-shadow: 0 10px 30px rgba(72, 187, 120, 0.5); }
            }
            .quest-completed {
                border: 2px solid #48bb78 !important;
                background: linear-gradient(135deg, rgba(72, 187, 120, 0.1), rgba(56, 161, 105, 0.1)) !important;
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Create sparkles effect
 */
function createSparkles(element) {
    const rect = element.getBoundingClientRect();
    const sparkleCount = 10;
    
    for (let i = 0; i < sparkleCount; i++) {
        const sparkle = document.createElement('div');
        sparkle.style.cssText = `
            position: fixed;
            width: 4px;
            height: 4px;
            background: gold;
            border-radius: 50%;
            pointer-events: none;
            z-index: 999;
            left: ${rect.left + Math.random() * rect.width}px;
            top: ${rect.top + Math.random() * rect.height}px;
            animation: sparkle-twinkle 0.8s ease-out forwards;
        `;
        
        document.body.appendChild(sparkle);
        setTimeout(() => sparkle.remove(), 800);
    }
    
    // Add sparkle animation
    if (!document.querySelector('#sparkle-animation')) {
        const style = document.createElement('style');
        style.id = 'sparkle-animation';
        style.textContent = `
            @keyframes sparkle-twinkle {
                0% { transform: scale(0); opacity: 1; }
                50% { transform: scale(1.5); opacity: 1; }
                100% { transform: scale(0); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Setup floating elements
 */
function setupFloatingElements() {
    const floatingElements = document.querySelectorAll('.main-avatar, .level-badge');
    
    floatingElements.forEach(element => {
        element.style.animation = `float ${3 + Math.random() * 2}s ease-in-out infinite`;
    });
}

/**
 * Setup rainbow cursor effect
 */
function setupRainbowCursor() {
    let mouseX = 0;
    let mouseY = 0;
    
    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });
    
    // Create rainbow trail
    setInterval(() => {
        if (Math.random() < 0.1) { // 10% chance per interval
            createRainbowParticle(mouseX, mouseY);
        }
    }, 50);
}

/**
 * Create rainbow particle
 */
function createRainbowParticle(x, y) {
    if (!animationsEnabled) return;
    
    const particle = document.createElement('div');
    const hue = Math.random() * 360;
    
    particle.style.cssText = `
        position: fixed;
        width: 6px;
        height: 6px;
        background: hsl(${hue}, 70%, 60%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 999;
        left: ${x}px;
        top: ${y}px;
        animation: rainbow-float 1s ease-out forwards;
    `;
    
    document.body.appendChild(particle);
    setTimeout(() => particle.remove(), 1000);
    
    // Add rainbow animation
    if (!document.querySelector('#rainbow-animations')) {
        const style = document.createElement('style');
        style.id = 'rainbow-animations';
        style.textContent = `
            @keyframes rainbow-float {
                0% {
                    transform: translate(0, 0) scale(1);
                    opacity: 1;
                }
                100% {
                    transform: translate(${(Math.random() - 0.5) * 50}px, -20px) scale(0);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Setup surprise animations
 */
function setupSurpriseAnimations() {
    // Random fun animations
    setInterval(() => {
        if (Math.random() < 0.05) { // 5% chance
            triggerSurpriseAnimation();
        }
    }, 30000); // Check every 30 seconds
}

/**
 * Trigger random surprise animation
 */
function triggerSurpriseAnimation() {
    const animations = [
        () => createFloatingEmoji('🌟'),
        () => createFloatingEmoji('🎈'),
        () => createFloatingEmoji('🦄'),
        () => createFloatingEmoji('🌈'),
        () => createMagicalBurst()
    ];
    
    const randomAnimation = animations[Math.floor(Math.random() * animations.length)];
    randomAnimation();
}

/**
 * Create floating emoji
 */
function createFloatingEmoji(emoji) {
    const element = document.createElement('div');
    element.textContent = emoji;
    element.style.cssText = `
        position: fixed;
        font-size: 2rem;
        pointer-events: none;
        z-index: 999;
        left: ${Math.random() * window.innerWidth}px;
        bottom: -50px;
        animation: float-emoji 5s linear forwards;
    `;
    
    document.body.appendChild(element);
    setTimeout(() => element.remove(), 5000);
    
    // Add float emoji animation
    if (!document.querySelector('#float-emoji-animation')) {
        const style = document.createElement('style');
        style.id = 'float-emoji-animation';
        style.textContent = `
            @keyframes float-emoji {
                0% {
                    transform: translateY(0) rotate(0deg);
                    opacity: 1;
                }
                100% {
                    transform: translateY(-150vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Create magical burst effect
 */
function createMagicalBurst() {
    const colors = ['#feca57', '#ff9ff3', '#48dbfb', '#ff6b6b'];
    const burstCount = 20;
    const centerX = window.innerWidth / 2;
    const centerY = window.innerHeight / 2;
    
    for (let i = 0; i < burstCount; i++) {
        const particle = document.createElement('div');
        const angle = (i / burstCount) * Math.PI * 2;
        const velocity = 5 + Math.random() * 10;
        
        particle.style.cssText = `
            position: fixed;
            width: 8px;
            height: 8px;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            border-radius: 50%;
            pointer-events: none;
            z-index: 999;
            left: ${centerX}px;
            top: ${centerY}px;
            animation: burst 2s ease-out forwards;
        `;
        
        document.body.appendChild(particle);
        setTimeout(() => particle.remove(), 2000);
        
        // Set individual particle animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes burst {
                0% {
                    transform: translate(0, 0) scale(1);
                    opacity: 1;
                }
                100% {
                    transform: translate(${Math.cos(angle) * velocity * 50}px, ${Math.sin(angle) * velocity * 50}px) scale(0);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Setup streak counter
 */
function setupStreakCounter() {
    const streakElement = document.querySelector('.streak-stat');
    if (streakElement) {
        const streak = parseInt(streakElement.textContent) || 0;
        
        if (streak > 0) {
            // Add fire animation for streaks
            if (streak >= 3) {
                streakElement.style.animation = 'fire-glow 2s ease-in-out infinite';
            }
            
            // Add fire animation
            if (!document.querySelector('#fire-animation')) {
                const style = document.createElement('style');
                style.id = 'fire-animation';
                style.textContent = `
                    @keyframes fire-glow {
                        0%, 100% { transform: scale(1); filter: brightness(1); }
                        50% { transform: scale(1.1); filter: brightness(1.2); }
                    }
                `;
                document.head.appendChild(style);
            }
        }
    }
}

/**
 * Check for new achievements
 */
function checkNewAchievements() {
    // This would normally make an API call
    // For now, simulate with random check
    if (Math.random() < 0.1) { // 10% chance
        const mockAchievement = {
            id: 1,
            title: 'First Steps!',
            description: 'You completed your first lesson!',
            points: 10,
            xp: 20
        };
        
        setTimeout(() => {
            document.dispatchEvent(new CustomEvent('achievementUnlocked', {
                detail: { achievement: mockAchievement }
            }));
        }, 2000);
    }
}

/**
 * Load kids preferences
 */
function loadKidsPreferences() {
    const saved = localStorage.getItem('miniminds_kids_prefs');
    if (saved) {
        try {
            const prefs = JSON.parse(saved);
            soundEnabled = prefs.soundEnabled !== false;
            animationsEnabled = prefs.animationsEnabled !== false;
            confettiEnabled = prefs.confettiEnabled !== false;
        } catch (e) {
            console.error('Error loading preferences:', e);
        }
    }
}

/**
 * Save kids preference
 */
function saveKidsPreference(key, value) {
    const saved = localStorage.getItem('miniminds_kids_prefs') || '{}';
    try {
        const prefs = JSON.parse(saved);
        prefs[key] = value;
        localStorage.setItem('miniminds_kids_prefs', JSON.stringify(prefs));
    } catch (e) {
        console.error('Error saving preference:', e);
    }
}

/**
 * Animate numeric value
 */
function animateValue(selector, start, end, duration) {
    const element = document.querySelector(selector);
    if (!element) return;
    
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// Export kids-specific functions
window.MiniMindsKids = {
    playSound,
    createConfetti,
    showAchievementPopup,
    createMagicalParticles,
    saveKidsPreference,
    animateValue
};