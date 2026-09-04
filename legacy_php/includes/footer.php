<?php
/**
 * MiniMinds Academy - Footer Template
 * Common footer with links and information
 */

$year = date('Y');
?>
    </main>
    
    <!-- Footer -->
    <footer class="<?php echo isChild() ? 'footer-kids' : 'footer-parent'; ?> mt-auto py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3">
                        <i class="fas fa-graduation-cap me-2"></i>
                        MiniMinds Academy
                    </h5>
                    <p class="text-muted">
                        Making learning fun and engaging for children aged 4-9 through gamification and interactive stories.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <?php if (isParent() || (!isChild() && !isParent())): ?>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/parents/" class="text-muted text-decoration-none">For Parents</a></li>
                        <?php endif; ?>
                        <?php if (isChild() || (!isChild() && !isParent())): ?>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/kids/" class="text-muted text-decoration-none">For Kids</a></li>
                        <?php endif; ?>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Courses</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Pricing</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Safety Guide</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h6 class="mb-3">Stay Updated</h6>
                    <p class="text-muted small">Get the latest updates and learning tips for your child.</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!isChild()): ?>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            COPPA Compliant • Safe for Kids
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted small mb-0">
                        © <?php echo $year; ?> MiniMinds Academy. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Made with <i class="fas fa-heart text-danger"></i> for young learners
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <?php if (isChild()): ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/kids.js"></script>
    <?php endif; ?>
    
    <!-- Sound Effects for Kids -->
    <?php if (isChild()): ?>
    <audio id="clickSound" preload="auto">
        <source src="<?php echo SITE_URL; ?>/assets/sounds/click.mp3" type="audio/mpeg">
    </audio>
    <audio id="successSound" preload="auto">
        <source src="<?php echo SITE_URL; ?>/assets/sounds/success.mp3" type="audio/mpeg">
    </audio>
    <audio id="levelUpSound" preload="auto">
        <source src="<?php echo SITE_URL; ?>/assets/sounds/levelup.mp3" type="audio/mpeg">
    </audio>
    <?php endif; ?>
    
</body>
</html>