<?php
/**
 * FitZone Fitness Center
 * Member Dashboard Home Page
 */

// Enable error reporting to help diagnose issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constant to allow inclusion of necessary files
define('FITZONE_APP', true);

// Include configuration and helper files
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../includes/authentication.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    initializeSession();
}

// Check if user is logged in, if not redirect to login page
if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Get current user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_role = $_SESSION['user_role'];

// Verify this is actually a member
if ($user_role !== 'member') {
    // Redirect to appropriate dashboard based on role
    if ($user_role === 'admin') {
        redirect('../admin/index.php');
    } elseif ($user_role === 'trainer') {
        redirect('../trainer/index.php');
    } else {
        redirect('../../login.php');
    }
}

// Get database connection
$db = getDb();

try {
    $user = $db->fetchSingle("SELECT * FROM users WHERE id = ?", [$user_id]);
    
    // If user not found, try with user_id column instead of id
    if (!$user) {
        $user = $db->fetchSingle("SELECT * FROM users WHERE user_id = ?", [$user_id]);
    }
    
    // If user still not found, logout and redirect
    if (!$user) {
        logout();
        redirect('../../login.php');
    }
} catch (Exception $e) {
    // If there's an error, continue anyway
}

// Check for active membership
$has_membership = false;
$days_remaining = 0;
try {
    $subscription = $db->fetchSingle(
        "SELECT * FROM member_subscriptions 
        WHERE user_id = ? 
        AND status = 'active' 
        AND end_date >= CURDATE() 
        ORDER BY end_date DESC 
        LIMIT 1",
        [$user_id]
    );
    
    if ($subscription) {
        $has_membership = $subscription;
        $end_date = new DateTime($subscription['end_date']);
        $today = new DateTime('today');
        $interval = $today->diff($end_date);
        $days_remaining = $interval->days;
    }
} catch (Exception $e) {
    // Silently handle error
}

// Fetch upcoming classes (if needed)
$upcoming_classes = [];
if ($has_membership) {
    try {
        $upcoming_classes = $db->fetchAll(
            "SELECT b.*, c.name as class_name, c.start_time, c.day_of_week, c.location
            FROM class_bookings b 
            JOIN fitness_classes c ON b.class_id = c.id 
            WHERE b.user_id = ? AND b.status = 'confirmed' AND b.booking_date >= CURDATE() 
            ORDER BY b.booking_date ASC, c.start_time ASC
            LIMIT 3",
            [$user_id]
        );
    } catch (Exception $e) {
        // Silently handle error
    }
}

// Create user_stats table if it doesn't exist (for progress data)
$db->query("
    CREATE TABLE IF NOT EXISTS `user_stats` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `date` DATE NOT NULL,
        `weight` DECIMAL(5,2) NULL,
        `body_fat` DECIMAL(5,2) NULL,
        `workout_duration` INT NULL,
        `calories_burned` INT NULL,
        `notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Check if the table fitness_classes exists and create it if it doesn't
$db->query("
    CREATE TABLE IF NOT EXISTS `fitness_classes` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT NULL,
        `trainer_id` INT NULL,
        `capacity` INT NOT NULL DEFAULT 20,
        `day_of_week` VARCHAR(10) NOT NULL,
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `location` VARCHAR(100) NOT NULL,
        `image` VARCHAR(255) NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Check if table class_bookings exists and create it if it doesn't
$db->query("
    CREATE TABLE IF NOT EXISTS `class_bookings` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `class_id` INT NOT NULL,
        `booking_date` DATE NOT NULL,
        `status` VARCHAR(20) NOT NULL DEFAULT 'confirmed',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Fetch latest stats
$latest_stats = null;
try {
    $latest_stats = $db->fetchSingle(
        "SELECT * FROM user_stats WHERE user_id = ? ORDER BY date DESC LIMIT 1",
        [$user_id]
    );
} catch (Exception $e) {
    // Silently handle error
}

// Get profile image URL (default if not set)
$profile_image = isset($user['profile_image']) && !empty($user['profile_image']) 
    ? '../../uploads/profile/' . $user['profile_image'] 
    : '../../assets/images/trainers/trainer-1.jpg';

// Current active sidebar item
$active_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - FitZone Fitness Center</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Common Dashboard styles -->
    <link href="../../assets/css/dashboard.css" rel="stylesheet">
    <!-- Black and White Theme Overrides -->
    <link href="../../assets/css/black-white-override.css" rel="stylesheet">
    <link href="../../assets/css/black-white-navbar.css" rel="stylesheet">
</head>
<body class="role-member black-white-theme">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" style="background-color: #fff; border-right: 1px solid #000;">
            <div class="sidebar-header" style="border-bottom: 1px solid #000; padding: 20px;">
                <div class="sidebar-brand">
                    <img src="../../assets/images/fitzone.png" alt="FitZone" style="height: 30px; filter: grayscale(100%);">
                </div>
            </div>
            
            <div class="sidebar-menu" style="padding-top: 15px;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li>
                        <a href="index.php" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'dashboard' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'dashboard' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-tachometer-alt"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'dashboard' ? 'bold' : 'normal'; ?>;">Dashboard</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="profile.php" class="<?php echo $active_page === 'profile' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'profile' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'profile' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-user"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'profile' ? 'bold' : 'normal'; ?>;">My Profile</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="membership.php" class="<?php echo $active_page === 'membership' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'membership' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'membership' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-id-card"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'membership' ? 'bold' : 'normal'; ?>;">My Membership</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="classes.php" class="<?php echo $active_page === 'classes' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'classes' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'classes' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-dumbbell"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'classes' ? 'bold' : 'normal'; ?>;">Classes</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="schedule.php" class="<?php echo $active_page === 'schedule' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'schedule' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'schedule' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-calendar-alt"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'schedule' ? 'bold' : 'normal'; ?>;">My Schedule</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="progress.php" class="<?php echo $active_page === 'progress' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'progress' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'progress' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-chart-line"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'progress' ? 'bold' : 'normal'; ?>;">My Progress</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="nutrition.php" class="<?php echo $active_page === 'nutrition' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'nutrition' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'nutrition' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-utensils"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'nutrition' ? 'bold' : 'normal'; ?>;">Nutrition Plans</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="messages.php" class="<?php echo $active_page === 'messages' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'messages' ? '#000' : 'transparent'; ?>; background-color: <?php echo $active_page === 'messages' ? '#f0f0f0' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-envelope"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'messages' ? 'bold' : 'normal'; ?>;">Messages</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="../../logout.php" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center;"><i class="fas fa-sign-out-alt"></i></span>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar -->
            <div class="topbar" style="background-color: #fff; border-bottom: 1px solid #000; padding: 15px; margin-bottom: 20px;">
                <div class="d-flex align-items-center">
                    <div class="toggle-sidebar me-3" style="color: #000; cursor: pointer;">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div class="topbar-title" style="color: #000; font-weight: 600; font-size: 1.2rem;">
                        Dashboard
                    </div>
                </div>
                
                <div class="topbar-right">
                    <div class="dropdown">
                        <div class="user-dropdown d-flex align-items-center" data-bs-toggle="dropdown" style="cursor: pointer;">
                            <span class="username d-none d-sm-inline me-2" style="color: #000; font-weight: 500;"><?php echo htmlspecialchars($username); ?></span>
                            <i class="fas fa-chevron-down small" style="color: #000;"></i>
                        </div>
                        
                        <ul class="dropdown-menu dropdown-menu-end" style="background-color: #fff; border: 1px solid #000; border-radius: 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <li><a class="dropdown-item" href="profile.php" style="color: #000; padding: 10px 15px;"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="../../logout.php" style="color: #000; padding: 10px 15px;"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="content">
                <!-- Welcome Banner -->
                <div class="welcome-banner mb-4" style="background-color: #f8f8f8; border: 1px solid #000; border-radius: 5px; padding: 20px;">
                    <div class="welcome-message">
                        <h3>Welcome back, <?php echo htmlspecialchars(isset($user['first_name']) ? $user['first_name'] : $username); ?>!</h3>
                        <p class="mb-0">
                            <?php if ($has_membership): ?>
                                You have <?php echo $days_remaining; ?> days remaining on your <?php echo $has_membership['membership_type']; ?> membership.
                            <?php else: ?>
                                You don't have an active membership. Visit the membership page to get started.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Stats Overview -->
                <div class="row mb-4">
                    <!-- Membership Status Widget -->
                    <div class="col-md-6 mb-3">
                        <div class="membership-info" style="background: #fff; border: 1px solid #000; border-radius: 5px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                            <div class="text-center mb-3">
                                <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #000; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 20px;">
                                    <i class="<?php echo $has_membership ? 'fas fa-check' : 'fas fa-exclamation'; ?>"></i>
                                </div>
                                <h5 style="color: #000; font-weight: bold;">Membership Status</h5>
                                <p class="mb-1">
                                    <?php if ($has_membership): ?>
                                        <span style="background-color: #000; color: #fff; padding: 3px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;">Active</span> <?php echo $has_membership['membership_type']; ?> Plan
                                    <?php else: ?>
                                        <span style="background-color: #000; color: #fff; padding: 3px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;">Inactive</span> No active membership
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <?php if ($has_membership): ?>
                                <div class="px-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span style="color: #000; font-weight: 500;">Membership Validity:</span>
                                        <span style="color: #000; font-weight: 500;"><?php echo $days_remaining; ?> days remaining</span>
                                    </div>
                                    <div class="progress" style="height: 10px; background-color: #eee; border-radius: 5px; overflow: hidden;">
                                        <?php 
                                        $total_days = 30; // Assuming monthly subscription
                                        if ($has_membership['duration'] === '6month') {
                                            $total_days = 180;
                                        } elseif ($has_membership['duration'] === '12month') {
                                            $total_days = 365;
                                        }
                                        $used_days = $total_days - $days_remaining;
                                        $percentage = ($used_days / $total_days) * 100;
                                        ?>
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%; background-color: #000;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span style="color: #666; font-size: 12px;">Start: <?php echo date('M d, Y', strtotime($has_membership['start_date'])); ?></span>
                                        <span style="color: #666; font-size: 12px;">End: <?php echo date('M d, Y', strtotime($has_membership['end_date'])); ?></span>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <a href="membership.php" class="btn btn-sm" style="border: 1px solid #000; color: #000; background-color: transparent; padding: 5px 15px; font-weight: 500; transition: all 0.3s;">View Details</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center">
                                    <p style="color: #333;">Get access to all our facilities and classes with a membership plan.</p>
                                    <a href="membership.php" class="btn" style="background-color: #000; color: #fff; border: none; padding: 8px 20px; font-weight: 500; border-radius: 4px;">Get Membership</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Stats Widgets -->
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="stat-box" style="background: #fff; border: 1px solid #000; border-radius: 5px; padding: 15px; position: relative; text-align: center; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                    <div class="stat-icon" style="position: absolute; top: 10px; right: 10px; color: #000;">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                    <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #000;"><?php echo count($upcoming_classes); ?></div>
                                    <div class="stat-label" style="font-size: 14px; color: #000;">Upcoming Classes</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-box" style="background: #fff; border: 1px solid #000; border-radius: 5px; padding: 15px; position: relative; text-align: center; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                    <div class="stat-icon" style="position: absolute; top: 10px; right: 10px; color: #000;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #000;"><?php echo $latest_stats ? $latest_stats['workout_duration'] : '0'; ?></div>
                                    <div class="stat-label" style="font-size: 14px; color: #000;">Workout Minutes</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box" style="background: #fff; border: 1px solid #000; border-radius: 5px; padding: 15px; position: relative; text-align: center; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                    <div class="stat-icon" style="position: absolute; top: 10px; right: 10px; color: #000;">
                                        <i class="fas fa-fire"></i>
                                    </div>
                                    <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #000;"><?php echo $latest_stats ? $latest_stats['calories_burned'] : '0'; ?></div>
                                    <div class="stat-label" style="font-size: 14px; color: #000;">Calories Burned</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box" style="background: #fff; border: 1px solid #000; border-radius: 5px; padding: 15px; position: relative; text-align: center; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                    <div class="stat-icon" style="position: absolute; top: 10px; right: 10px; color: #000;">
                                        <i class="fas fa-weight"></i>
                                    </div>
                                    <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #000;"><?php echo $latest_stats ? $latest_stats['weight'] : '--'; ?></div>
                                    <div class="stat-label" style="font-size: 14px; color: #000;">Weight (kg)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Access Links -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="widget" style="background-color: #fff; border: 1px solid #000; border-radius: 5px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                            <div class="widget-header" style="margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px;">
                                <div class="widget-title" style="font-size: 18px; font-weight: bold; color: #000;">Quick Access</div>
                            </div>
                            <div class="widget-body">
                                <div class="quick-links" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                                    <a href="classes.php" class="quick-link" style="display: flex; align-items: center; padding: 15px; text-decoration: none; color: #000; border: 1px solid #000; border-radius: 5px; transition: all 0.3s ease;">
                                        <div class="quick-link-icon" style="margin-right: 15px; font-size: 24px;">
                                            <i class="fas fa-dumbbell"></i>
                                        </div>
                                        <div>
                                            <div class="quick-link-title" style="font-weight: bold; margin-bottom: 5px;">Classes</div>
                                            <div class="quick-link-desc" style="font-size: 12px; color: #555;">Browse and book fitness classes</div>
                                        </div>
                                    </a>
                                    
                                    <a href="schedule.php" class="quick-link" style="display: flex; align-items: center; padding: 15px; text-decoration: none; color: #000; border: 1px solid #000; border-radius: 5px; transition: all 0.3s ease;">
                                        <div class="quick-link-icon" style="margin-right: 15px; font-size: 24px;">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div>
                                            <div class="quick-link-title" style="font-weight: bold; margin-bottom: 5px;">Schedule</div>
                                            <div class="quick-link-desc" style="font-size: 12px; color: #555;">View and manage your schedule</div>
                                        </div>
                                    </a>
                                    
                                    <a href="progress.php" class="quick-link" style="display: flex; align-items: center; padding: 15px; text-decoration: none; color: #000; border: 1px solid #000; border-radius: 5px; transition: all 0.3s ease;">
                                        <div class="quick-link-icon" style="margin-right: 15px; font-size: 24px;">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <div class="quick-link-title" style="font-weight: bold; margin-bottom: 5px;">Progress</div>
                                            <div class="quick-link-desc" style="font-size: 12px; color: #555;">Track your fitness progress</div>
                                        </div>
                                    </a>
                                    
                                    <a href="nutrition.php" class="quick-link" style="display: flex; align-items: center; padding: 15px; text-decoration: none; color: #000; border: 1px solid #000; border-radius: 5px; transition: all 0.3s ease;">
                                        <div class="quick-link-icon" style="margin-right: 15px; font-size: 24px;">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                        <div>
                                            <div class="quick-link-title" style="font-weight: bold; margin-bottom: 5px;">Nutrition</div>
                                            <div class="quick-link-desc" style="font-size: 12px; color: #555;">View nutrition plans and tips</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('body').classList.toggle('sidebar-collapsed');
        });
        
        // Auto-collapse sidebar on small screens
        function checkScreenSize() {
            if (window.innerWidth < 992) {
                document.querySelector('body').classList.add('sidebar-collapsed');
            }
        }
        
        // Check on load
        checkScreenSize();
        
        // Check on resize
        window.addEventListener('resize', checkScreenSize);
    });
    </script>
</body>
</html>