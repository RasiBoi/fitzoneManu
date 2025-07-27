<?php
/**
 * FitZone Fitness Center
 * Member Membership Page
 */

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

$db = getDb();
$user = $db->fetchSingle("SELECT * FROM users WHERE id = ?", [$user_id]);

// If user not found, try with user_id column
if (!$user) {
    $user = $db->fetchSingle("SELECT * FROM users WHERE user_id = ?", [$user_id]);
}

// If user still not found, logout and redirect
if (!$user) {
    logout();
    redirect('../../login.php');
}

// Create membership_plans table if it doesn't exist
$db->query("
    CREATE TABLE IF NOT EXISTS `membership_plans` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `price_monthly` DECIMAL(10,2) NOT NULL,
        `price_quarterly` DECIMAL(10,2) NOT NULL,
        `price_annual` DECIMAL(10,2) NOT NULL,
        `features` TEXT,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Add sample membership plans if none exist
$count = $db->fetchSingle("SELECT COUNT(*) as count FROM membership_plans");
if (!$count || $count['count'] == 0) {
    $db->query("
        INSERT INTO membership_plans (name, description, price_monthly, price_quarterly, price_annual, features) VALUES
        ('Basic', 'Access to basic gym facilities and equipment', 39.99, 99.99, 359.88, 'Gym access during regular hours|Basic fitness equipment|Locker room access|Free fitness assessment'),
        ('Premium', 'Full access to all gym facilities and select classes', 59.99, 149.99, 539.88, 'All Basic features|Access to all fitness classes|Swimming pool access|Sauna & steam room|Unlimited guest passes|Personal trainer consultation (1x/month)'),
        ('Elite', 'VIP experience with all amenities and services', 99.99, 249.99, 899.88, 'All Premium features|24/7 gym access|Priority class booking|Personal trainer sessions (2x/month)|Nutrition planning|Massage therapy (1x/month)|VIP locker|Towel service')
    ");
}

// Create member_subscriptions table if it doesn't exist
$db->query("
    CREATE TABLE IF NOT EXISTS `member_subscriptions` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `membership_plan_id` INT NOT NULL,
        `payment_id` VARCHAR(100),
        `membership_type` VARCHAR(50) NOT NULL,
        `duration` VARCHAR(20) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `amount_paid` DECIMAL(10,2) NOT NULL,
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `status` VARCHAR(20) NOT NULL DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Get membership plans
try {
    $membership_plans = $db->fetchAll("SELECT * FROM membership_plans WHERE is_active = 1 ORDER BY price_monthly ASC");
    if ($membership_plans === false) {
        $membership_plans = array(); // Set to empty array if query fails
    }
} catch (Exception $e) {
    // Log error and set to empty array
    error_log('Error fetching membership plans: ' . $e->getMessage());
    $membership_plans = array();
}

// Check if user has an active membership
$active_membership = $db->fetchSingle(
    "SELECT s.*, p.name as plan_name, p.description as plan_description, p.features as plan_features 
    FROM member_subscriptions s 
    JOIN membership_plans p ON s.membership_plan_id = p.id
    WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE() 
    ORDER BY s.end_date DESC 
    LIMIT 1",
    [$user_id]
);

// Get membership history
$membership_history = $db->fetchAll(
    "SELECT s.*, p.name as plan_name 
    FROM member_subscriptions s 
    LEFT JOIN membership_plans p ON s.membership_plan_id = p.id
    WHERE s.user_id = ? 
    ORDER BY s.start_date DESC 
    LIMIT 10",
    [$user_id]
);

// Handle membership purchase
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_membership'])) {
    error_log('Purchase form submitted: ' . print_r($_POST, true)); // Log POST data for debugging
    $plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
    $duration = isset($_POST['duration']) ? sanitize($_POST['duration']) : '';
    
    // Validate inputs
    if (!$plan_id || !in_array($duration, ['monthly', 'quarterly', 'annual'])) {
        $error_message = 'Invalid membership plan or duration selected.';
    } else {
        // Get plan details
        $plan = $db->fetchSingle("SELECT * FROM membership_plans WHERE id = ? AND is_active = 1", [$plan_id]);
        
        if (!$plan) {
            $error_message = 'Selected membership plan not found.';
        } else {
            // Set price based on duration
            $price = 0;
            switch ($duration) {
                case 'monthly':
                    $price = $plan['price_monthly'];
                    $months = 1;
                    break;
                case 'quarterly':
                    $price = $plan['price_quarterly'];
                    $months = 3;
                    break;
                case 'annual':
                    $price = $plan['price_annual'];
                    $months = 12;
                    break;
            }
            
            // Set dates
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+$months months"));
            
            // Simulate payment (in real-world scenario, integrate with payment gateway)
            $payment_id = 'PAY-' . strtoupper(substr(md5(time() . $user_id), 0, 10));
            
            // Create subscription record
            $result = $db->query(
                "INSERT INTO member_subscriptions 
                (user_id, membership_plan_id, payment_id, membership_type, duration, price, amount_paid, start_date, end_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')",
                [$user_id, $plan_id, $payment_id, $plan['name'], $duration, $price, $price, $start_date, $end_date]
            );
            
            if ($result) {
                $success_message = "Congratulations! Your {$plan['name']} membership has been successfully purchased. Your membership is now active.";
                
                // Get the newly created subscription
                $active_membership = $db->fetchSingle(
                    "SELECT s.*, p.name as plan_name, p.description as plan_description, p.features as plan_features 
                    FROM member_subscriptions s 
                    JOIN membership_plans p ON s.membership_plan_id = p.id
                    WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE() 
                    ORDER BY s.end_date DESC 
                    LIMIT 1",
                    [$user_id]
                );
                
                // Get updated membership history
                $membership_history = $db->fetchAll(
                    "SELECT s.*, p.name as plan_name 
                    FROM member_subscriptions s 
                    LEFT JOIN membership_plans p ON s.membership_plan_id = p.id
                    WHERE s.user_id = ? 
                    ORDER BY s.start_date DESC 
                    LIMIT 10",
                    [$user_id]
                );
            } else {
                $error_message = 'Failed to process your membership purchase. Please try again.';
            }
        }
    }
}

// Handle membership cancelation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_membership'])) {
    $subscription_id = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
    
    // Check if subscription belongs to user and is active
    $subscription = $db->fetchSingle(
        "SELECT * FROM member_subscriptions WHERE id = ? AND user_id = ? AND status = 'active'",
        [$subscription_id, $user_id]
    );
    
    if (!$subscription) {
        $error_message = 'Invalid subscription selected or subscription is not active.';
    } else {
        // Cancel subscription
        $result = $db->query(
            "UPDATE member_subscriptions SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
            [$subscription_id]
        );
        
        if ($result) {
            $success_message = 'Your membership has been cancelled. You will still have access until the end of your current billing period.';
            
            // Update active membership status
            $active_membership = $db->fetchSingle(
                "SELECT s.*, p.name as plan_name, p.description as plan_description, p.features as plan_features 
                FROM member_subscriptions s 
                JOIN membership_plans p ON s.membership_plan_id = p.id
                WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE() 
                ORDER BY s.end_date DESC 
                LIMIT 1",
                [$user_id]
            );
            
            // Get updated membership history
            $membership_history = $db->fetchAll(
                "SELECT s.*, p.name as plan_name 
                FROM member_subscriptions s 
                LEFT JOIN membership_plans p ON s.membership_plan_id = p.id
                WHERE s.user_id = ? 
                ORDER BY s.start_date DESC 
                LIMIT 10",
                [$user_id]
            );
        } else {
            $error_message = 'Failed to cancel your membership. Please try again or contact support.';
        }
    }
}

// Calculate days remaining if there's an active membership
$days_remaining = 0;
if ($active_membership) {
    $end_date = new DateTime($active_membership['end_date']);
    $today = new DateTime('today');
    $interval = $today->diff($end_date);
    $days_remaining = $interval->days;
}

// Set page title
$page_title = 'My Membership';

// Get profile image URL (default if not set)
$profile_image = isset($user['profile_image']) && !empty($user['profile_image']) 
    ? '../../uploads/profile/' . $user['profile_image'] 
    : '../../assets/images/trainers/trainer-1.jpg';

// Current active sidebar item
$active_page = 'membership';
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
    <!-- Custom Dashboard styles -->
    <link href="../../assets/css/dashboard.css" rel="stylesheet">
    <!-- Black and White Theme Overrides -->
    <link href="../../assets/css/black-white-override.css" rel="stylesheet">
    <link href="../../assets/css/black-white-navbar.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" style="background-color: #fff; border-right: 2px solid #f7931e;">
            <div class="sidebar-header" style="border-bottom: 1px solid #f7931e; padding: 20px;">
                <div class="sidebar-brand">
                    <img src="../../assets/images/fitzone.png" alt="FitZone" style="height: 30px;">
                </div>
            </div>
            
            <div class="sidebar-menu" style="padding-top: 15px;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li>
                        <a href="index.php" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'dashboard' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'dashboard' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'dashboard' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-tachometer-alt"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'dashboard' ? 'bold' : 'normal'; ?>;">Dashboard</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="profile.php" class="<?php echo $active_page === 'profile' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'profile' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'profile' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'profile' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-user"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'profile' ? 'bold' : 'normal'; ?>;">My Profile</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="membership.php" class="<?php echo $active_page === 'membership' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'membership' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'membership' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'membership' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-id-card"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'membership' ? 'bold' : 'normal'; ?>;">My Membership</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="classes.php" class="<?php echo $active_page === 'classes' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'classes' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'classes' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'classes' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-dumbbell"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'classes' ? 'bold' : 'normal'; ?>;">Classes</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="schedule.php" class="<?php echo $active_page === 'schedule' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'schedule' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'schedule' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'schedule' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-calendar-alt"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'schedule' ? 'bold' : 'normal'; ?>;">My Schedule</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="progress.php" class="<?php echo $active_page === 'progress' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'progress' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'progress' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'progress' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-chart-line"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'progress' ? 'bold' : 'normal'; ?>;">My Progress</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="nutrition.php" class="<?php echo $active_page === 'nutrition' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'nutrition' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'nutrition' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'nutrition' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-utensils"></i></span>
                            <span style="font-weight: <?php echo $active_page === 'nutrition' ? 'bold' : 'normal'; ?>;">Nutrition Plans</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="messages.php" class="<?php echo $active_page === 'messages' ? 'active' : ''; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #000; text-decoration: none; border-left: 4px solid <?php echo $active_page === 'messages' ? '#f7931e' : 'transparent'; ?>; background-color: <?php echo $active_page === 'messages' ? 'rgba(247, 147, 30, 0.1)' : 'transparent'; ?>;">
                            <span class="menu-icon" style="margin-right: 10px; width: 20px; text-align: center; color: <?php echo $active_page === 'messages' ? '#f7931e' : '#000'; ?>;"><i class="fas fa-envelope"></i></span>
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
            <div class="topbar" style="background-color: #fff; border-bottom: 1px solid #f7931e; padding: 15px; margin-bottom: 20px;">
                <div class="d-flex align-items-center">
                    <div class="toggle-sidebar me-3" style="color: #f7931e; cursor: pointer;">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div class="topbar-title" style="color: #000; font-weight: 600; font-size: 1.2rem;">
                        My Membership
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
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 style="color: #f7931e; font-weight: bold;">My Membership</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">My Membership</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="background-color: #fff; color: #000; border: 2px solid #f7931e;">
                    <i class="fas fa-check-circle me-2" style="color: #f7931e;"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="background-color: #f7931e; opacity: 0.8;"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background-color: #fff; color: #000; border: 2px solid #f7931e;">
                    <i class="fas fa-exclamation-circle me-2" style="color: #f7931e;"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="background-color: #f7931e; opacity: 0.8;"></button>
                </div>
                <?php endif; ?>
                
                <!-- Tabs for Membership Navigation -->
                <ul class="nav mb-4" id="membershipTab" role="tablist" style="border-bottom: 1px solid #ddd; margin-bottom: 20px; padding-bottom: 0;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $active_membership ? 'active' : ''; ?>" id="active-membership-tab" data-bs-toggle="tab" data-bs-target="#active-membership" type="button" role="tab" aria-controls="active-membership" aria-selected="<?php echo $active_membership ? 'true' : 'false'; ?>" style="border: none; border-bottom: 2px solid <?php echo $active_membership ? '#f7931e' : 'transparent'; ?>; border-radius: 0; color: <?php echo $active_membership ? '#f7931e' : '#666'; ?>; font-weight: <?php echo $active_membership ? '600' : 'normal'; ?>; padding: 10px 20px; margin-right: 5px; background: transparent;">
                            <i class="fas fa-id-card me-2"></i>Active Membership
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo !$active_membership ? 'active' : ''; ?>" id="available-plans-tab" data-bs-toggle="tab" data-bs-target="#available-plans" type="button" role="tab" aria-controls="available-plans" aria-selected="<?php echo !$active_membership ? 'true' : 'false'; ?>" style="border: none; border-bottom: 2px solid <?php echo !$active_membership ? '#f7931e' : 'transparent'; ?>; border-radius: 0; color: <?php echo !$active_membership ? '#f7931e' : '#666'; ?>; font-weight: <?php echo !$active_membership ? '600' : 'normal'; ?>; padding: 10px 20px; margin-right: 5px; background: transparent;">
                            <i class="fas fa-list me-2"></i>Available Plans
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="membership-history-tab" data-bs-toggle="tab" data-bs-target="#membership-history" type="button" role="tab" aria-controls="membership-history" aria-selected="false" style="border: none; border-bottom: 2px solid transparent; border-radius: 0; color: #666; padding: 10px 20px; margin-right: 5px; background: transparent;">
                            <i class="fas fa-history me-2"></i>Membership History
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="membershipTabContent">
                    <!-- Active Membership Tab -->
                    <div class="tab-pane fade <?php echo $active_membership ? 'show active' : ''; ?>" id="active-membership" role="tabpanel" aria-labelledby="active-membership-tab">
                        <?php if ($active_membership): ?>
                            <div style="background-color: #f8f8f8; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 style="font-weight: 600; color: #000; margin-bottom: 15px;">
                                            <i class="fas fa-star me-2" style="color: #f7931e;"></i>
                                            <?php echo htmlspecialchars($active_membership['plan_name']); ?> Membership
                                        </h5>
                                        <p style="color: #333; margin-bottom: 15px;">
                                            <?php echo htmlspecialchars($active_membership['plan_description']); ?>
                                        </p>
                                        <div style="margin-bottom: 15px;">
                                            <span style="background-color: #f7931e; color: #fff; padding: 6px 12px; border-radius: 4px; display: inline-block; font-size: 14px;">
                                                <i class="fas fa-check-circle me-1"></i> Active
                                            </span>
                                        </div>
                                        <p style="color: #333; margin-bottom: 0;">
                                            <strong style="font-weight: 600; color: #000;">Started On:</strong> <?php echo date('F j, Y', strtotime($active_membership['start_date'])); ?><br>
                                            <strong style="font-weight: 600; color: #000;">Expires On:</strong> <?php echo date('F j, Y', strtotime($active_membership['end_date'])); ?><br>
                                            <strong style="font-weight: 600; color: #000;">Membership Type:</strong> <?php echo ucfirst($active_membership['duration']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <form method="post" onsubmit="return confirm('Are you sure you want to cancel your membership?');">
                                            <input type="hidden" name="subscription_id" value="<?php echo $active_membership['id']; ?>">
                                            <button type="submit" name="cancel_membership" style="border: 1px solid #f7931e; background-color: transparent; color: #f7931e; padding: 8px 16px; border-radius: 4px; transition: all 0.3s;">
                                                <i class="fas fa-times me-2"></i>Cancel Membership
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Days Remaining Countdown -->
                            <div style="background-color: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                                <h5 style="font-weight: 600; color: #000; margin-bottom: 20px;">Time Remaining in Your Membership</h5>
                                <div style="display: flex; justify-content: space-around; text-align: center;">
                                    <div style="flex: 1;">
                                        <div style="font-size: 36px; font-weight: 700; color: #000; margin-bottom: 5px;"><?php echo $days_remaining; ?></div>
                                        <div style="color: #666; font-size: 14px;">Days</div>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 36px; font-weight: 700; color: #000; margin-bottom: 5px;"><?php echo floor($days_remaining / 7); ?></div>
                                        <div style="color: #666; font-size: 14px;">Weeks</div>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 36px; font-weight: 700; color: #000; margin-bottom: 5px;"><?php echo ceil($days_remaining / 30); ?></div>
                                        <div style="color: #666; font-size: 14px;">Months</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Membership Features -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Membership Benefits</h5>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $features = explode('|', $active_membership['plan_features']);
                                    echo '<ul class="membership-features">';
                                    foreach ($features as $feature) {
                                        echo '<li><i class="fas fa-check"></i> ' . htmlspecialchars($feature) . '</li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> You don't have an active membership. Check out our available plans and get started today!
                            </div>
                            <div class="text-center mb-4">
                                <button class="btn btn-primary" data-bs-toggle="tab" data-bs-target="#available-plans" style="background-color: #f7931e; border-color: #f7931e; color: #fff;">
                                    <i class="fas fa-id-card me-2"></i>View Available Plans
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Available Plans Tab -->
                    <div class="tab-pane fade <?php echo !$active_membership ? 'show active' : ''; ?>" id="available-plans" role="tabpanel" aria-labelledby="available-plans-tab">
                        <!-- Option selection form (hidden form for purchase submission) -->
                        <form id="purchaseForm" method="post">
                            <input type="hidden" name="plan_id" id="selected_plan_id" value="<?php echo isset($membership_plans[0]['id']) ? $membership_plans[0]['id'] : ''; ?>">
                            <input type="hidden" name="duration" id="selected_duration" value="monthly">
                            
                            <?php if ($active_membership): ?>
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle me-2"></i> You already have an active membership that expires on <?php echo date('F j, Y', strtotime($active_membership['end_date'])); ?>. If you purchase a new plan, it will become effective after your current membership expires.
                                </div>
                            <?php endif; ?>
                            
                            <!-- Duration Selection -->
                            <div class="card mb-4" style="border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div class="card-header" style="background-color: #fff; border-bottom: 1px solid #eee; padding: 15px 20px;">
                                    <h5 class="mb-0" style="font-weight: 600; color: #000;">Choose Membership Duration</h5>
                                </div>
                                <div class="card-body">
                                    <div class="duration-selection" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                                        <div class="duration-option selected" data-duration="monthly" style="flex: 1; min-width: 180px; border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; text-align: center; transition: all 0.2s; background-color: #f8f8f8;">
                                            <div class="duration-title" style="font-weight: 600; font-size: 18px; margin-bottom: 5px;">Monthly</div>
                                            <div class="duration-price" style="font-size: 14px; color: #333;">Pay Monthly</div>
                                            <div class="duration-savings" style="font-size: 12px; color: #666; margin-top: 5px;">Standard Rate</div>
                                        </div>
                                        <div class="duration-option" data-duration="quarterly" style="flex: 1; min-width: 180px; border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; text-align: center; transition: all 0.2s;">
                                            <div class="duration-title" style="font-weight: 600; font-size: 18px; margin-bottom: 5px;">Quarterly</div>
                                            <div class="duration-price" style="font-size: 14px; color: #333;">Save 15%</div>
                                            <div class="duration-savings" style="font-size: 12px; color: #666; margin-top: 5px;">Billed every 3 months</div>
                                        </div>
                                        <div class="duration-option" data-duration="annual" style="flex: 1; min-width: 180px; border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; text-align: center; transition: all 0.2s;">
                                            <div class="duration-title" style="font-weight: 600; font-size: 18px; margin-bottom: 5px;">Annual</div>
                                            <div class="duration-price" style="font-size: 14px; color: #333;">Save 25%</div>
                                            <div class="duration-savings" style="font-size: 12px; color: #666; margin-top: 5px;">Best value</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Membership Plans -->
                            <div class="row">
                                <?php 
                                // Ensure membership_plans is an array before iteration
                                if (!empty($membership_plans) && is_array($membership_plans)):
                                    foreach ($membership_plans as $index => $plan): 
                                ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card membership-card h-100 <?php echo $index === 0 ? 'selected' : ''; ?> plan-<?php echo strtolower($plan['name']); ?>" data-plan-id="<?php echo $plan['id']; ?>" style="border: 1px solid <?php echo $index === 0 ? '#f7931e' : '#ddd'; ?>; border-radius: 8px; overflow: hidden; transition: all 0.3s; <?php echo $index === 0 ? 'box-shadow: 0 0 15px rgba(247,147,30,0.1);' : ''; ?>">
                                            <div class="card-header text-center" style="background-color: <?php echo $index === 0 ? '#f7931e' : '#f8f8f8'; ?>; border-bottom: 1px solid #ddd; padding: 15px;">
                                                <h4 class="membership-name" style="margin: 0; font-weight: 600; color: <?php echo $index === 0 ? '#fff' : '#000'; ?>;"><?php echo htmlspecialchars($plan['name']); ?></h4>
                                            </div>
                                            <div class="card-body" style="padding: 20px;">
                                                <div class="text-center mb-4">
                                                    <p class="membership-price" style="font-size: 32px; font-weight: 700; margin-bottom: 0; color: #000;">$<span class="price-value"><?php echo number_format($plan['price_monthly'], 2); ?></span></p>
                                                    <p class="membership-price-period" style="font-size: 14px; color: #666;">per month</p>
                                                </div>
                                                
                                                <p style="color: #333; text-align: center;"><?php echo htmlspecialchars($plan['description']); ?></p>
                                                
                                                <h6 class="mt-4 mb-3" style="font-weight: 600; color: #000;">Key Benefits</h6>
                                                <?php 
                                                $features = explode('|', $plan['features']);
                                                echo '<ul class="membership-features" style="list-style: none; padding-left: 0;">';
                                                foreach ($features as $feature) {
                                                    echo '<li style="margin-bottom: 10px; display: flex; align-items: flex-start;"><i class="fas fa-check" style="color: #f7931e; margin-right: 10px; margin-top: 3px;"></i> <span style="color: #333;">' . htmlspecialchars($feature) . '</span></li>';
                                                }
                                                echo '</ul>';
                                                ?>
                                                
                                                <div class="text-center mt-4">
                                                    <button type="button" class="btn select-plan-btn" style="border: 1px solid #f7931e; color: <?php echo $index === 0 ? '#fff' : '#f7931e'; ?>; background-color: <?php echo $index === 0 ? '#f7931e' : 'transparent'; ?>; padding: 8px 16px; transition: all 0.3s;">
                                                        <i class="fas fa-check-circle me-2"></i><?php echo $index === 0 ? 'Selected' : 'Select Plan'; ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <div class="col-12">
                                        <div class="alert" style="background-color: #f8f8f8; color: #000; border: 1px solid #ddd; border-radius: 5px; padding: 15px;">
                                            <i class="fas fa-info-circle me-2"></i> No membership plans are currently available. Please check back later.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Purchase Button -->
                            <div class="text-center mt-4 mb-5">
                                <button type="submit" name="purchase_membership" class="btn btn-lg" style="background-color: #f7931e; color: #fff; padding: 12px 30px; border-radius: 4px; border: none; font-weight: 500; transition: all 0.3s; box-shadow: 0 2px 10px rgba(247,147,30,0.1); cursor: pointer;">
                                    <i class="fas fa-shopping-cart me-2"></i>Purchase Membership
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Membership History Tab -->
                    <div class="tab-pane fade" id="membership-history" role="tabpanel" aria-labelledby="membership-history-tab">
                        <?php if (empty($membership_history)): ?>
                            <div class="alert" style="background-color: #f8f8f8; color: #000; border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-top: 20px;">
                                <i class="fas fa-info-circle me-2"></i> You don't have any membership history yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive" style="margin-top: 20px;">
                                <table class="table" style="border-collapse: separate; border-spacing: 0; width: 100%; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                                    <thead>
                                        <tr style="background-color: #f7931e; color: #fff;">
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Plan</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Duration</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Start Date</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">End Date</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Amount Paid</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($membership_history as $history): ?>
                                            <tr style="border-bottom: 1px solid #eee;">
                                                <td style="padding: 12px 15px; color: #333;"><?php echo htmlspecialchars(isset($history['membership_type']) ? $history['membership_type'] : ''); ?></td>
                                                <td style="padding: 12px 15px; color: #333;"><?php echo ucfirst($history['duration']); ?></td>
                                                <td style="padding: 12px 15px; color: #333;"><?php echo date('m/d/Y', strtotime($history['start_date'])); ?></td>
                                                <td style="padding: 12px 15px; color: #333;"><?php echo date('m/d/Y', strtotime($history['end_date'])); ?></td>
                                                <td style="padding: 12px 15px; color: #333;">$<?php echo number_format($history['amount_paid'], 2); ?></td>
                                                <td style="padding: 12px 15px;">
                                                    <?php if ($history['status'] === 'active' && strtotime($history['end_date']) >= time()): ?>
                                                        <span style="background-color: #f7931e; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px;">Active</span>
                                                    <?php elseif ($history['status'] === 'cancelled'): ?>
                                                        <span style="background-color: #f8f8f8; color: #333; border: 1px solid #ddd; padding: 5px 10px; border-radius: 4px; font-size: 12px;">Cancelled</span>
                                                    <?php else: ?>
                                                        <span style="background-color: #f8f8f8; color: #333; border: 1px solid #ddd; padding: 5px 10px; border-radius: 4px; font-size: 12px;">Expired</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
        
        // Handle membership plan selection
        const membershipCards = document.querySelectorAll('.membership-card');
        const planIdInput = document.getElementById('selected_plan_id');
        
        membershipCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                membershipCards.forEach(c => {
                    c.classList.remove('selected');
                    c.style.borderColor = '#ddd';
                    c.style.boxShadow = 'none';
                    const cardHeader = c.querySelector('.card-header');
                    if (cardHeader) {
                        cardHeader.style.backgroundColor = '#f8f8f8';
                        const title = cardHeader.querySelector('.membership-name');
                        if (title) title.style.color = '#000';
                    }
                    const btn = c.querySelector('.select-plan-btn');
                    if (btn) {
                        btn.style.backgroundColor = 'transparent';
                        btn.style.color = '#f7931e';
                        btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Select Plan';
                    }
                });
                
                // Add selected class to clicked card
                this.classList.add('selected');
                this.style.borderColor = '#f7931e';
                this.style.boxShadow = '0 0 15px rgba(247,147,30,0.1)';
                const header = this.querySelector('.card-header');
                if (header) {
                    header.style.backgroundColor = '#f7931e';
                    const title = header.querySelector('.membership-name');
                    if (title) title.style.color = '#fff';
                }
                
                const selectBtn = this.querySelector('.select-plan-btn');
                if (selectBtn) {
                    selectBtn.style.backgroundColor = '#f7931e';
                    selectBtn.style.color = '#fff';
                    selectBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Selected';
                }
                
                // Update hidden input with selected plan ID
                planIdInput.value = this.dataset.planId;
            });
        });
        
        // Handle duration selection
        const durationOptions = document.querySelectorAll('.duration-option');
        const durationInput = document.getElementById('selected_duration');
        const priceElements = document.querySelectorAll('.price-value');
        const periodElements = document.querySelectorAll('.membership-price-period');
        
        // Tab switching with proper styling
        const tabLinks = document.querySelectorAll('#membershipTab .nav-link');
        tabLinks.forEach(tab => {
            tab.addEventListener('click', function() {
                tabLinks.forEach(t => {
                    t.style.borderBottom = '2px solid transparent';
                    t.style.color = '#666';
                    t.style.fontWeight = 'normal';
                });
                
                this.style.borderBottom = '2px solid #f7931e';
                this.style.color = '#f7931e';
                this.style.fontWeight = '600';
            });
        });
        
        // Get price data from PHP
        const prices = {
            <?php 
            if (!empty($membership_plans) && is_array($membership_plans)):
                foreach ($membership_plans as $plan): 
            ?>
            <?php echo $plan['id']; ?>: {
                monthly: <?php echo $plan['price_monthly']; ?>,
                quarterly: <?php echo $plan['price_quarterly']; ?>,
                annual: <?php echo $plan['price_annual']; ?>
            },
            <?php 
                endforeach;
            endif;
            ?>
        };
        
        // Initialize membership plan cards
        if (membershipCards.length > 0) {
            // Set the first card as selected
            const firstCard = membershipCards[0];
            firstCard.classList.add('selected');
            firstCard.style.borderColor = '#f7931e';
            firstCard.style.boxShadow = '0 0 15px rgba(247,147,30,0.1)';
            
            const header = firstCard.querySelector('.card-header');
            if (header) {
                header.style.backgroundColor = '#f7931e';
                const title = header.querySelector('.membership-name');
                if (title) title.style.color = '#fff';
            }
            
            const selectBtn = firstCard.querySelector('.select-plan-btn');
            if (selectBtn) {
                selectBtn.style.backgroundColor = '#f7931e';
                selectBtn.style.color = '#fff';
                selectBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Selected';
            }
            
            // Set the initial plan ID value
            if (planIdInput && firstCard.dataset.planId) {
                planIdInput.value = firstCard.dataset.planId;
            }
        }

        // Initialize by ensuring the first duration option is properly selected
        if (durationOptions.length > 0) {
            const firstOption = durationOptions[0];
            firstOption.classList.add('selected');
            firstOption.style.backgroundColor = 'rgba(247, 147, 30, 0.1)';
            firstOption.style.borderColor = '#f7931e';
            if (durationInput) {
                durationInput.value = firstOption.dataset.duration || 'monthly';
            }
        }
        
        durationOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options and reset background
                durationOptions.forEach(o => {
                    o.classList.remove('selected');
                    o.style.backgroundColor = '';
                    o.style.borderColor = '#ddd';
                });
                
                // Add selected class and styling to clicked option
                this.classList.add('selected');
                this.style.backgroundColor = 'rgba(247, 147, 30, 0.1)';
                this.style.borderColor = '#f7931e';
                
                // Update hidden input with selected duration
                const duration = this.dataset.duration;
                if (durationInput) {
                    durationInput.value = duration;
                }
                
                // Update prices for all plans
                membershipCards.forEach(card => {
                    const planId = card.dataset.planId;
                    const priceElement = card.querySelector('.price-value');
                    const periodElement = card.querySelector('.membership-price-period');
                    
                    if (priceElement && periodElement && prices[planId]) {
                        const price = prices[planId][duration];
                        priceElement.textContent = price.toFixed(2);
                        
                        // Update period text
                        switch(duration) {
                            case 'monthly':
                                periodElement.textContent = 'per month';
                                break;
                            case 'quarterly':
                                periodElement.textContent = 'per quarter';
                                break;
                            case 'annual':
                                periodElement.textContent = 'per year';
                                break;
                        }
                    }
                });
            });
        });
        
        // Select Plan Button Functionality
        const selectPlanBtns = document.querySelectorAll('.select-plan-btn');
        
        selectPlanBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent any default button action
                const card = this.closest('.membership-card');
                
                // Reset all cards styling
                membershipCards.forEach(c => {
                    c.classList.remove('selected');
                    c.style.borderColor = '#ddd';
                    c.style.boxShadow = 'none';
                    const header = c.querySelector('.card-header');
                    if (header) {
                        header.style.backgroundColor = '#f8f8f8';
                        const title = header.querySelector('.membership-name');
                        if (title) title.style.color = '#000';
                    }
                    const selectBtn = c.querySelector('.select-plan-btn');
                    if (selectBtn) {
                        selectBtn.style.backgroundColor = 'transparent';
                        selectBtn.style.color = '#f7931e';
                        selectBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Select Plan';
                    }
                });
                
                // Update selected card styling
                card.classList.add('selected');
                card.style.borderColor = '#f7931e';
                card.style.boxShadow = '0 0 15px rgba(247,147,30,0.1)';
                const header = card.querySelector('.card-header');
                if (header) {
                    header.style.backgroundColor = '#f7931e';
                    const title = header.querySelector('.membership-name');
                    if (title) title.style.color = '#fff';
                }
                
                // Update button styling
                this.style.backgroundColor = '#f7931e';
                this.style.color = '#fff';
                this.innerHTML = '<i class="fas fa-check-circle me-2"></i>Selected';
                
                // Update the hidden input with the selected plan ID
                const planId = card.dataset.planId;
                document.getElementById('selected_plan_id').value = planId;
            });
        });
    });
    </script>
</body>
</html>
