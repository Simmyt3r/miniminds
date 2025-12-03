<?php
/**
 * MiniMinds Academy - Main Landing Page
 * Public homepage with information about the platform
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'MiniMinds Academy - Fun Learning for Kids';

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-white">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-3 fw-bold mb-4">
                        <span class="badge bg-warning text-dark p-2 me-2">🎓</span>
                        MiniMinds Academy
                    </h1>
                    <h2 class="h3 mb-4">Where Learning Meets Adventure!</h2>
                    <p class="lead mb-4">
                        Transform screen time into learning time with our gamified platform designed for children aged 4-9. 
                        Watch your kids fall in love with coding, business basics, and interactive stories!
                    </p>
                    
                    <div class="hero-buttons mb-4">
                        <div class="btn-group" role="group">
                            <a href="parents/register.php" class="btn btn-light btn-lg me-3">
                                <i class="fas fa-user-plus me-2"></i>Start Free Trial
                            </a>
                            <a href="parents/login.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Parent Login
                            </a>
                        </div>
                    </div>
                    
                    <div class="hero-features">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt fa-2x"></i>
                                </div>
                                <small>COPPA Safe</small>
                            </div>
                            <div class="col-4">
                                <div class="feature-icon">
                                    <i class="fas fa-gamepad fa-2x"></i>
                                </div>
                                <small>Gamified</small>
                            </div>
                            <div class="col-4">
                                <div class="feature-icon">
                                    <i class="fas fa-trophy fa-2x"></i>
                                </div>
                                <small>Rewarding</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="assets/images/hero-kids.png" alt="Kids Learning" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Wave Animation -->
    <div class="wave-bottom">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
        </svg>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-4 fw-bold mb-3">Why Kids & Parents Love MiniMinds</h2>
                <p class="lead text-muted">Discover the perfect blend of education and entertainment</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon-large mb-3">
                            <i class="fas fa-rocket text-primary fa-3x"></i>
                        </div>
                        <h4>Interactive Learning</h4>
                        <p class="text-muted">
                            Engaging stories and games that teach coding basics, business fundamentals, 
                            and problem-solving skills through play.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card feature-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon-large mb-3">
                            <i class="fas fa-chart-line text-success fa-3x"></i>
                        </div>
                        <h4>Progress Tracking</h4>
                        <p class="text-muted">
                            Real-time dashboards for parents to monitor learning progress, 
                            achievements, and screen time management.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card feature-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon-large mb-3">
                            <i class="fas fa-medal text-warning fa-3x"></i>
                        </div>
                        <h4>Gamification System</h4>
                        <p class="text-muted">
                            Points, badges, levels, and virtual rewards that keep children 
                            motivated and excited to learn every day.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Courses Preview Section -->
<section class="courses-preview py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-4 fw-bold mb-3">Learning Adventures</h2>
                <p class="lead text-muted">Age-appropriate courses designed by education experts</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card course-preview-card h-100 border-0 shadow">
                    <div class="card-body text-center p-4">
                        <div class="course-icon mb-3">
                            <i class="fas fa-code fa-3x text-primary"></i>
                        </div>
                        <h4>Coding Basics</h4>
                        <p class="text-muted mb-3">
                            Introduction to programming concepts through fun stories and games
                        </p>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success me-2"></i>Problem Solving</li>
                            <li><i class="fas fa-check text-success me-2"></i>Logical Thinking</li>
                            <li><i class="fas fa-check text-success me-2"></i>Creativity</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card course-preview-card h-100 border-0 shadow">
                    <div class="card-body text-center p-4">
                        <div class="course-icon mb-3">
                            <i class="fas fa-briefcase fa-3x text-success"></i>
                        </div>
                        <h4>Business Fundamentals</h4>
                        <p class="text-muted mb-3">
                            Learn about money, entrepreneurship, and basic business concepts
                        </p>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success me-2"></i>Financial Literacy</li>
                            <li><i class="fas fa-check text-success me-2"></i>Entrepreneurship</li>
                            <li><i class="fas fa-check text-success me-2"></i>Decision Making</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card course-preview-card h-100 border-0 shadow">
                    <div class="card-body text-center p-4">
                        <div class="course-icon mb-3">
                            <i class="fas fa-book fa-3x text-warning"></i>
                        </div>
                        <h4>Interactive Stories</h4>
                        <p class="text-muted mb-3">
                            Engaging narratives that teach valuable life lessons and skills
                        </p>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success me-2"></i>Reading Skills</li>
                            <li><i class="fas fa-check text-success me-2"></i>Comprehension</li>
                            <li><i class="fas fa-check text-success me-2"></i>Vocabulary</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing-section py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-4 fw-bold mb-3">Simple, Affordable Pricing</h2>
                <p class="lead text-muted">Choose the perfect plan for your family</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4">
                <div class="card pricing-card h-100">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">Free Plan</h4>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="price-display mb-3">
                            <span class="currency">₦</span>
                            <span class="amount">0</span>
                            <span class="period">/month</span>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>1 Child</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>5 Lessons</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Basic Progress Tracking</li>
                            <li class="mb-2 text-muted"><i class="fas fa-times me-2"></i>Advanced Reports</li>
                            <li class="mb-2 text-muted"><i class="fas fa-times me-2"></i>Priority Support</li>
                        </ul>
                        <button class="btn btn-outline-primary w-100">Get Started</button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card pricing-card h-100 border-primary">
                    <div class="card-header bg-success text-white text-center py-3 position-relative">
                        <span class="badge bg-warning text-dark position-absolute top-0 start-50 translate-middle mt-1">POPULAR</span>
                        <h4 class="mb-0">Premium Plan</h4>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="price-display mb-3">
                            <span class="currency">₦</span>
                            <span class="amount">500</span>
                            <span class="period">/month</span>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>2 Children</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited Lessons</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced Reports</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Email Notifications</li>
                            <li class="mb-2 text-muted"><i class="fas fa-times me-2"></i>Priority Support</li>
                        </ul>
                        <button class="btn btn-success w-100">Start Free Trial</button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card pricing-card h-100">
                    <div class="card-header bg-warning text-dark text-center py-3">
                        <h4 class="mb-0">Family Plan</h4>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="price-display mb-3">
                            <span class="currency">₦</span>
                            <span class="amount">1,500</span>
                            <span class="period">/month</span>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>5 Children</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>All Premium Features</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Priority Support</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Family Reports</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Early Access</li>
                        </ul>
                        <button class="btn btn-warning w-100">Choose Family</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-4 fw-bold mb-3">What Parents Are Saying</h2>
                <p class="lead text-muted">Real stories from real families</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card testimonial-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-3">
                            "My 6-year-old daughter loves the coding games! She's learning problem-solving 
                            skills without even realizing it's educational."
                        </p>
                        <div class="d-flex align-items-center">
                            <img src="https://i.pravatar.cc/150?img=32" alt="Parent" class="rounded-circle me-3" width="50">
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Mother of Emma, 6</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card testimonial-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-3">
                            "The parent dashboard gives me peace of mind knowing exactly what my kids 
                            are learning and how they're progressing."
                        </p>
                        <div class="d-flex align-items-center">
                            <img src="https://i.pravatar.cc/150?img=12" alt="Parent" class="rounded-circle me-3" width="50">
                            <div>
                                <h6 class="mb-0">Michael Chen</h6>
                                <small class="text-muted">Father of Lucas & Mia</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card testimonial-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-3">
                            "Finally, screen time I feel good about! My 8-year-old is learning business 
                            concepts while having fun with the games."
                        </p>
                        <div class="d-flex align-items-center">
                            <img src="https://i.pravatar.cc/150?img=25" alt="Parent" class="rounded-circle me-3" width="50">
                            <div>
                                <h6 class="mb-0">Amanda Davis</h6>
                                <small class="text-muted">Mother of Jordan, 8</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white border-0 shadow-lg">
                    <div class="card-body text-center p-5">
                        <h2 class="display-5 fw-bold mb-4">Ready to Start the Learning Adventure?</h2>
                        <p class="lead mb-4">
                            Join thousands of parents who trust MiniMinds Academy for their children's education
                        </p>
                        <div class="cta-buttons">
                            <a href="parents/register.php" class="btn btn-light btn-lg me-3">
                                <i class="fas fa-rocket me-2"></i>Start Free Trial Today
                            </a>
                            <a href="#" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-play-circle me-2"></i>Watch Demo
                            </a>
                        </div>
                        <p class="mt-4 mb-0">
                            <small>No credit card required • Cancel anytime • COPPA compliant</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.feature-icon-large {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.course-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.price-display {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary-color);
}

.currency {
    font-size: 2rem;
    vertical-align: super;
}

.amount {
    font-size: 3rem;
}

.period {
    font-size: 1.2rem;
    color: #6c757d;
}

.pricing-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pricing-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.stars i {
    font-size: 1.2rem;
}

.testimonial-card {
    transition: transform 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
}

.wave-bottom {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}

.wave-bottom svg {
    position: relative;
    display: block;
    width: calc(100% + 1.3px);
    height: 120px;
}

.cta-section .card {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

@media (max-width: 768px) {
    .hero-content {
        text-align: center;
    }
    
    .hero-buttons .btn-group {
        flex-direction: column;
    }
    
    .hero-buttons .btn {
        margin-bottom: 1rem;
    }
    
    .price-display {
        font-size: 2rem;
    }
    
    .amount {
        font-size: 2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>