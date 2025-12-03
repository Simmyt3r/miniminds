/**
 * MiniMinds Academy - Main JavaScript
 * Core functionality for the learning platform
 */

// Global variables
let currentUserId = null;
let currentChildId = null;
let csrfToken = null;

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    initializePlatform();
    setupEventListeners();
    checkAuthStatus();
    initializeAnimations();
});

/**
 * Initialize platform settings
 */
function initializePlatform() {
    // Get CSRF token from meta tag or form
    csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                document.querySelector('input[name="csrf_token"]')?.value;
    
    // Set up user info
    currentUserId = window.currentUser?.id || null;
    currentChildId = window.childUser?.id || null;
    
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Initialize popovers
    if (typeof bootstrap !== 'undefined') {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Form submissions
    document.addEventListener('submit', handleFormSubmit);
    
    // AJAX links
    document.addEventListener('click', handleAjaxLinks);
    
    // Auto-save for forms
    setupAutoSave();
    
    // Notification handling
    setupNotifications();
    
    // Session timeout warning
    setupSessionTimeout();
}

/**
 * Handle form submissions with AJAX
 */
function handleFormSubmit(event) {
    const form = event.target;
    
    // Skip if form doesn't have ajax-handler class
    if (!form.classList.contains('ajax-handler')) {
        return;
    }
    
    event.preventDefault();
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    // Create FormData
    const formData = new FormData(form);
    
    // Send AJAX request
    fetch(form.action || window.location.href, {
        method: form.method || 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Operation successful!', 'success');
            
            // Handle redirect
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
            
            // Reset form if requested
            if (form.dataset.reset === 'true') {
                form.reset();
            }
            
            // Trigger custom event
            form.dispatchEvent(new CustomEvent('ajaxSuccess', { detail: data }));
            
        } else {
            showNotification(data.message || 'Operation failed. Please try again.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        showNotification('A network error occurred. Please check your connection.', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/**
 * Handle AJAX links
 */
function handleAjaxLinks(event) {
    const link = event.target.closest('a');
    
    if (!link || !link.dataset.ajax) {
        return;
    }
    
    event.preventDefault();
    
    const url = link.href;
    const confirmMessage = link.dataset.confirm;
    
    if (confirmMessage && !confirm(confirmMessage)) {
        return;
    }
    
    // Show loading
    link.classList.add('loading');
    
    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        link.classList.remove('loading');
        
        if (data.success) {
            showNotification(data.message || 'Operation successful!', 'success');
            
            // Update UI if needed
            if (data.update) {
                updatePageContent(data.update);
            }
            
            // Trigger custom event
            link.dispatchEvent(new CustomEvent('ajaxSuccess', { detail: data }));
            
        } else {
            showNotification(data.message || 'Operation failed.', 'danger');
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        link.classList.remove('loading');
        showNotification('A network error occurred.', 'danger');
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to container
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
    
    container.appendChild(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
    
    // Initialize Bootstrap alert
    if (typeof bootstrap !== 'undefined') {
        const bsAlert = new bootstrap.Alert(notification);
    }
}

/**
 * Setup auto-save functionality
 */
function setupAutoSave() {
    const autoSaveForms = document.querySelectorAll('[data-auto-save]');
    
    autoSaveForms.forEach(form => {
        let saveTimeout;
        
        form.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                autoSaveForm(form);
            }, 2000);
        });
    });
}

/**
 * Auto-save form data
 */
function autoSaveForm(form) {
    const formData = new FormData(form);
    
    fetch(form.dataset.autoSave, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show subtle auto-save indicator
            const indicator = form.querySelector('.auto-save-indicator');
            if (indicator) {
                indicator.innerHTML = '<i class="fas fa-check text-success"></i> Saved';
                setTimeout(() => {
                    indicator.innerHTML = '';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error('Auto-save error:', error);
    });
}

/**
 * Setup notifications
 */
function setupNotifications() {
    // Check for browser notification permission
    if ('Notification' in window) {
        if (Notification.permission === 'default') {
            // Request permission on user interaction
            document.addEventListener('click', function requestNotificationPermission() {
                Notification.requestPermission();
                document.removeEventListener('click', requestNotificationPermission);
            }, { once: true });
        }
    }
    
    // Setup real-time notifications (WebSocket placeholder)
    // In production, implement WebSocket connection
    setupRealtimeNotifications();
}

/**
 * Setup real-time notifications
 */
function setupRealtimeNotifications() {
    // WebSocket implementation would go here
    // For now, simulate with periodic checks
    if (currentUserId || currentChildId) {
        setInterval(() => {
            checkForNewNotifications();
        }, 30000); // Check every 30 seconds
    }
}

/**
 * Check for new notifications
 */
function checkForNotifications() {
    fetch('/api/notifications/check', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.notifications && data.notifications.length > 0) {
            data.notifications.forEach(notification => {
                showNotification(notification.message, notification.type);
                
                // Show browser notification if permitted
                if (Notification.permission === 'granted') {
                    new Notification(notification.title, {
                        body: notification.message,
                        icon: '/assets/images/favicon.ico'
                    });
                }
            });
        }
    })
    .catch(error => {
        console.error('Notification check error:', error);
    });
}

/**
 * Setup session timeout warning
 */
function setupSessionTimeout() {
    const timeout = 25 * 60 * 1000; // 25 minutes
    let warningTimeout;
    let logoutTimeout;
    
    function resetTimeouts() {
        clearTimeout(warningTimeout);
        clearTimeout(logoutTimeout);
        
        warningTimeout = setTimeout(() => {
            showSessionWarning();
        }, timeout);
        
        logoutTimeout = setTimeout(() => {
            logout();
        }, timeout + (5 * 60 * 1000)); // 5 minutes after warning
    }
    
    // Reset on user activity
    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetTimeouts, true);
    });
    
    resetTimeouts();
}

/**
 * Show session timeout warning
 */
function showSessionWarning() {
    const warning = document.createElement('div');
    warning.className = 'modal show';
    warning.style.cssText = 'display: block; background: rgba(0,0,0,0.5);';
    warning.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Session Timeout Warning</h5>
                </div>
                <div class="modal-body">
                    <p>Your session will expire in 5 minutes due to inactivity.</p>
                    <p>Click "Stay Logged In" to continue your session.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="logout()">Logout</button>
                    <button class="btn btn-primary" onclick="extendSession()">Stay Logged In</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(warning);
}

/**
 * Extend user session
 */
function extendSession() {
    // Remove warning modal
    const warning = document.querySelector('.modal.show');
    if (warning && warning.textContent.includes('Session Timeout Warning')) {
        warning.remove();
    }
    
    // Ping server to extend session
    fetch('/api/session/extend', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(() => {
        showNotification('Session extended', 'success');
    })
    .catch(error => {
        console.error('Session extend error:', error);
    });
    
    // Reset timeouts
    setupSessionTimeout();
}

/**
 * Logout user
 */
function logout() {
    fetch('/includes/auth.php', {
        method: 'POST',
        body: new FormData(document.createElement('form')),
        credentials: 'same-origin'
    })
    .then(() => {
        window.location.href = '/parents/login.php';
    })
    .catch(error => {
        console.error('Logout error:', error);
        window.location.href = '/parents/login.php';
    });
}

/**
 * Initialize animations
 */
function initializeAnimations() {
    // Intersection Observer for scroll animations
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, {
            threshold: 0.1
        });
        
        // Observe elements with animation classes
        document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .bounce-in').forEach(el => {
            observer.observe(el);
        });
    }
    
    // Initialize particle effects for kids theme
    if (document.body.classList.contains('kids-theme')) {
        initializeParticleEffects();
    }
}

/**
 * Initialize particle effects for kids
 */
function initializeParticleEffects() {
    const canvas = document.createElement('canvas');
    canvas.id = 'particle-canvas';
    canvas.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
        opacity: 0.3;
    `;
    document.body.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');
    const particles = [];
    const particleCount = 50;
    
    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    
    function createParticle() {
        return {
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * 0.5,
            vy: (Math.random() - 0.5) * 0.5,
            size: Math.random() * 3 + 1,
            color: `hsl(${Math.random() * 60 + 200}, 70%, 60%)`
        };
    }
    
    function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach(particle => {
            particle.x += particle.vx;
            particle.y += particle.vy;
            
            // Wrap around edges
            if (particle.x < 0) particle.x = canvas.width;
            if (particle.x > canvas.width) particle.x = 0;
            if (particle.y < 0) particle.y = canvas.height;
            if (particle.y > canvas.height) particle.y = 0;
            
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            ctx.fillStyle = particle.color;
            ctx.fill();
        });
        
        requestAnimationFrame(animateParticles);
    }
    
    // Initialize
    resizeCanvas();
    for (let i = 0; i < particleCount; i++) {
        particles.push(createParticle());
    }
    
    // Handle resize
    window.addEventListener('resize', resizeCanvas);
    
    // Start animation
    animateParticles();
}

/**
 * Update page content dynamically
 */
function updatePageContent(updates) {
    if (updates.html) {
        updates.html.forEach(update => {
            const element = document.querySelector(update.selector);
            if (element) {
                element.innerHTML = update.content;
            }
        });
    }
    
    if (updates.data) {
        updates.data.forEach(update => {
            const element = document.querySelector(update.selector);
            if (element) {
                element[update.property] = update.value;
            }
        });
    }
    
    if (updates.classes) {
        updates.classes.forEach(update => {
            const element = document.querySelector(update.selector);
            if (element) {
                element.classList.toggle(update.class, update.add);
            }
        });
    }
}

/**
 * Check authentication status
 */
function checkAuthStatus() {
    // Check remember me token
    const rememberToken = getCookie('remember_token');
    if (rememberToken && !currentUserId && !currentChildId) {
        // Token exists but no session - attempt auto-login
        fetch('/api/auth/check-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ token: rememberToken })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Token check error:', error);
        });
    }
}

/**
 * Get cookie value
 */
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

/**
 * Format time ago
 */
function timeAgo(date) {
    const now = new Date();
    const past = new Date(date);
    const diffMs = now - past;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffSecs < 60) return 'just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    
    return past.toLocaleDateString();
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for use in other scripts
window.MiniMinds = {
    showNotification,
    logout,
    extendSession,
    timeAgo,
    debounce,
    throttle,
    updatePageContent
};