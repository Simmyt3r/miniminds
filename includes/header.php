<?php
/**
 * MiniMinds Academy - Header Template
 * Common header for all pages with navigation and branding
 */

require_once 'config.php';
require_once 'functions.php';

// Check for remember me token on every page load
//if (!isset($_SESSION['user_id']) && !isset($_SESSION['child_id'])) {
 //   checkRememberMeToken();
///}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'MiniMinds Academy'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <!-- Google Fonts - Kid Friendly -->
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <?php if (isChild()): ?>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/kids-style.css">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
</head>
<body class="<?php echo isChild() ? 'kids-theme' : 'parent-theme'; ?>">
    
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark <?php echo isChild() ? 'navbar-kids' : 'navbar-parent'; ?> sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>/">
                <i class="fas fa-graduation-cap me-2"></i>
                <span class="brand-text">MiniMinds Academy</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isParent()): ?>
                <!-- Parent Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/parents/dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/parents/children.php">
                            <i class="fas fa-child me-1"></i> Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/parents/reports.php">
                            <i class="fas fa-chart-line me-1"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/parents/settings.php">
                            <i class="fas fa-cog me-1"></i> Settings
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text text-muted">
                                <small>Plan: <span class="badge bg-<?php echo $_SESSION['subscription_plan'] === 'free' ? 'secondary' : 'success'; ?>">
                                    <?php echo ucfirst($_SESSION['subscription_plan']); ?>
                                </span></small>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-credit-card me-2"></i> Subscription</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="includes/auth.php">
                                    <input type="hidden" name="action" value="logout">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
                
                <?php elseif (isChild()): ?>
                <!-- Child Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/kids/dashboard.php">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/kids/play.php">
                            <i class="fas fa-play me-1"></i> Play
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/kids/profile.php">
                            <i class="fas fa-user me-1"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/kids/store.php">
                            <i class="fas fa-store me-1"></i> Store
                        </a>
                    </li>
                </ul>
                
                <!-- Child Stats Bar -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="fas fa-star text-warning me-1"></i>
                            Level <?php echo $_SESSION['child_level']; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="fas fa-bolt text-primary me-1"></i>
                            <?php echo $_SESSION['child_xp']; ?> XP
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="fas fa-coins text-warning me-1"></i>
                            <?php echo $_SESSION['child_points']; ?> Points
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text">
                            <img src="<?php echo SITE_URL; ?>/assets/images/avatars/<?php echo htmlspecialchars($_SESSION['child_avatar']); ?>" 
                                 alt="Avatar" class="avatar-nav rounded-circle">
                            <?php echo htmlspecialchars($_SESSION['child_display_name']); ?>
                        </span>
                    </li>
                </ul>
                
                <?php else: ?>
                <!-- Public Navigation -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/parents/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Parent Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/parents/register.php">
                            <i class="fas fa-user-plus me-1"></i> Sign Up
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="container mt-3">
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['flash_message']);
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?php echo isChild() ? 'kids-main' : 'parent-main'; ?>">