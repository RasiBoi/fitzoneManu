<?php
/**
 * FitZone Fitness Center
 * Member Classes Page
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

// Check if user has an active membership
$has_membership = false;
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
        $has_membership = true;
    }
} catch (Exception $e) {
    // Silently handle error
}

// Handle class booking
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_class'])) {
    if (!$has_membership) {
        $error_message = 'You need an active membership to book classes. Please purchase a membership first.';
    } else {
        $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
        
        // Check if class exists
        $class = $db->fetchSingle("SELECT * FROM classes WHERE id = ?", [$class_id]);
        
        if (!$class) {
            $error_message = 'Selected class not found.';
        } else {
            // Check if user already booked this class
            $existing_booking = $db->fetchSingle(
                "SELECT * FROM class_bookings 
                WHERE user_id = ? AND class_id = ?",
                [$user_id, $class_id]
            );
            
            if ($existing_booking) {
                $error_message = 'You have already booked this class.';
            } else {
                // Check if class is full
                $current_bookings = $db->fetchSingle(
                    "SELECT COUNT(*) as count FROM class_bookings WHERE class_id = ?",
                    [$class_id]
                );
                
                if ($current_bookings['count'] >= $class['capacity']) {
                    $error_message = 'This class is already full. Please choose another class.';
                } else {
                    // Book the class
                    $result = $db->query(
                        "INSERT INTO class_bookings (user_id, class_id, booking_date) 
                        VALUES (?, ?, NOW())",
                        [$user_id, $class_id]
                    );
                    
                    if ($result) {
                        $success_message = 'Class booked successfully! You are now registered for ' . $class['name'] . '.';
                    } else {
                        $error_message = 'Failed to book class. Please try again.';
                    }
                }
            }
        }
    }
}

// Cancel class booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    
    // Check if booking exists and belongs to user
    $booking = $db->fetchSingle(
        "SELECT b.*, c.name as class_name 
        FROM class_bookings b 
        JOIN classes c ON b.class_id = c.id 
        WHERE b.id = ? AND b.user_id = ?",
        [$booking_id, $user_id]
    );
    
    if (!$booking) {
        $error_message = 'Booking not found or you do not have permission to cancel it.';
    } else {
        // Check if cancellation is allowed (e.g. not within 12 hours of class)
        $class_time = strtotime($booking['class_date'] . ' ' . $booking['start_time']);
        $now = time();
        $hours_diff = ($class_time - $now) / 3600;
        
        if ($hours_diff < 12) {
            $error_message = 'Cancellations must be made at least 12 hours before the class starts.';
        } else {
            // Cancel booking
            $result = $db->query(
                "DELETE FROM class_bookings WHERE id = ? AND user_id = ?",
                [$booking_id, $user_id]
            );
            
            if ($result) {
                $success_message = 'Your booking for ' . $booking['class_name'] . ' has been cancelled.';
            } else {
                $error_message = 'Failed to cancel booking. Please try again.';
            }
        }
    }
}

// Get all available classes
$classes = [];
try {
    $classes = $db->fetchAll(
        "SELECT c.*, t.name as trainer_name, t.specialization 
        FROM classes c 
        LEFT JOIN trainers t ON c.trainer_id = t.id 
        WHERE c.status = 'active' 
        ORDER BY c.class_date ASC, c.start_time ASC"
    );
    
    // Ensure $classes is an array
    if (!is_array($classes)) {
        $classes = [];
    }
} catch (Exception $e) {
    // If table doesn't exist, create it with sample data
    $db->query("
        CREATE TABLE IF NOT EXISTS `classes` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT,
            `class_date` DATE NOT NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `capacity` INT NOT NULL DEFAULT 20,
            `trainer_id` INT,
            `location` VARCHAR(100),
            `status` ENUM('active', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS `trainers` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `specialization` VARCHAR(100),
            `bio` TEXT,
            `profile_image` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Create class_bookings table
    $db->query("
        CREATE TABLE IF NOT EXISTS `class_bookings` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `class_id` INT NOT NULL,
            `booking_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `status` ENUM('booked', 'cancelled', 'attended', 'no-show') NOT NULL DEFAULT 'booked',
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Insert sample trainers
    $db->query("
        INSERT IGNORE INTO trainers (id, name, specialization, bio) VALUES
        (1, 'John Smith', 'HIIT, CrossFit', 'John specializes in high intensity training and CrossFit. He has 8 years of experience in fitness training.'),
        (2, 'Sarah Johnson', 'Yoga, Pilates', 'Sarah is our yoga expert with over 10 years of experience in mindful movement practices.'),
        (3, 'Mike Wilson', 'Strength Training', 'Mike focuses on strength training and muscle building techniques with 6 years of experience.');
    ");
    
    // Insert sample classes
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $day_after = date('Y-m-d', strtotime('+2 days'));
    
    $db->query("
        INSERT IGNORE INTO classes (name, description, class_date, start_time, end_time, capacity, trainer_id, location, status) VALUES
        ('Morning HIIT', 'High Intensity Interval Training to kickstart your day', '$tomorrow', '07:00:00', '08:00:00', 15, 1, 'Studio A', 'active'),
        ('Yoga Flow', 'Relaxing yoga flow to improve flexibility and mindfulness', '$tomorrow', '10:00:00', '11:00:00', 20, 2, 'Studio B', 'active'),
        ('Strength & Conditioning', 'Build strength and improve conditioning', '$tomorrow', '18:00:00', '19:00:00', 12, 3, 'Main Gym', 'active'),
        ('Core Blast', 'Focus on core muscles for a stronger center', '$day_after', '08:00:00', '09:00:00', 15, 1, 'Studio A', 'active'),
        ('Pilates', 'Improve posture and core strength through Pilates', '$day_after', '11:00:00', '12:00:00', 15, 2, 'Studio B', 'active'),
        ('Evening HIIT', 'High Intensity Interval Training to end your day', '$day_after', '19:00:00', '20:00:00', 15, 1, 'Studio A', 'active')
    ");
    
    // Fetch classes again
    $classes = $db->fetchAll(
        "SELECT c.*, t.name as trainer_name, t.specialization 
        FROM classes c 
        LEFT JOIN trainers t ON c.trainer_id = t.id 
        WHERE c.status = 'active' 
        ORDER BY c.class_date ASC, c.start_time ASC"
    );
}

// Group classes by date
$classes_by_date = [];
if (is_array($classes)) {
    foreach ($classes as $class) {
        $date = $class['class_date'];
        if (!isset($classes_by_date[$date])) {
            $classes_by_date[$date] = [];
        }
        $classes_by_date[$date][] = $class;
    }
}

// Get user's booked classes
$booked_classes = [];
try {
    $booked_classes = $db->fetchAll(
        "SELECT b.id as booking_id, c.id as class_id, c.name, c.description, c.class_date, c.start_time, c.end_time, c.location, t.name as trainer_name
        FROM class_bookings b
        JOIN classes c ON b.class_id = c.id
        LEFT JOIN trainers t ON c.trainer_id = t.id
        WHERE b.user_id = ? AND b.status = 'booked' AND c.class_date >= CURDATE()
        ORDER BY c.class_date ASC, c.start_time ASC",
        [$user_id]
    );
} catch (Exception $e) {
    // Handle error silently
}

// Set page title
$page_title = 'Classes';

// Get profile image URL (default if not set)
$profile_image = isset($user['profile_image']) && !empty($user['profile_image']) 
    ? '../../uploads/profile/' . $user['profile_image'] 
    : '../../assets/images/trainers/trainer-1.jpg';

// Current active sidebar item
$active_page = 'classes';
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
</head>
<body class="role-member">
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
                    <div class="topbar-title" style="color: #f7931e; font-weight: 600; font-size: 1.2rem;">
                        Classes
                    </div>
                </div>
                
                <div class="topbar-right">
                    <div class="dropdown">
                        <div class="user-dropdown d-flex align-items-center" data-bs-toggle="dropdown" style="cursor: pointer;">
                            <img src="<?php echo $profile_image; ?>" alt="Profile" class="profile-img" style="width: 32px; height: 32px; border-radius: 50%; margin-right: 8px;">
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
                        <h4 style="color: #f7931e; font-weight: bold;">Fitness Classes</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Classes</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <?php if (!$has_membership): ?>
                <div class="alert alert-warning" role="alert" style="background-color: #fff; color: #000; border: 2px solid #f7931e;">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x" style="color: #f7931e;"></i>
                        </div>
                        <div>
                            <h5 style="color: #000000ff;">Active Membership Required</h5>
                            <p class="mb-0">You need an active membership to book fitness classes. <a href="membership.php" class="alert-link" style="color: #f7931e;">Purchase a membership</a> to unlock this feature.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
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
                
                <!-- Tabs for Class Navigation -->
                <ul class="nav mb-4" id="classTab" role="tablist" style="border-bottom: 1px solid #ddd; margin-bottom: 20px; padding-bottom: 0;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-classes-tab" data-bs-toggle="tab" data-bs-target="#all-classes" type="button" role="tab" aria-controls="all-classes" aria-selected="true" style="border: none; border-bottom: 2px solid #f7931e; border-radius: 0; color: #f7931e; font-weight: 600; padding: 10px 20px; margin-right: 5px; background: transparent;">
                            <i class="fas fa-list me-2"></i>Available Classes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="my-bookings-tab" data-bs-toggle="tab" data-bs-target="#my-bookings" type="button" role="tab" aria-controls="my-bookings" aria-selected="false" style="border: none; border-bottom: 2px solid transparent; border-radius: 0; color: #666; padding: 10px 20px; margin-right: 5px; background: transparent;">
                            <i class="fas fa-calendar-check me-2"></i>My Bookings (<?php echo count($booked_classes); ?>)
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="classTabContent">
                    <!-- All Classes Tab -->
                    <div class="tab-pane fade show active" id="all-classes" role="tabpanel" aria-labelledby="all-classes-tab">
                        <?php if (empty($classes)): ?>
                            <div class="alert" style="background-color: #f8f8f8; color: #000; border: 1px solid #ddd; border-radius: 5px; padding: 15px;">
                                <i class="fas fa-info-circle me-2" style="color: #f7931e;"></i> No classes are currently available. Please check back later.
                            </div>
                        <?php else: ?>
                            <?php foreach ($classes_by_date as $date => $day_classes): ?>
                                <div class="class-date-header" style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f7931e;">
                                    <h5 class="mb-0" style="color: #f7931e; font-weight: 600;">
                                        <?php 
                                        $date_obj = new DateTime($date);
                                        $today = new DateTime('today');
                                        $tomorrow = new DateTime('tomorrow');
                                        
                                        if ($date_obj->format('Y-m-d') === $today->format('Y-m-d')) {
                                            echo 'Today - ' . $date_obj->format('F j, Y');
                                        } elseif ($date_obj->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                                            echo 'Tomorrow - ' . $date_obj->format('F j, Y');
                                        } else {
                                            echo $date_obj->format('l, F j, Y');
                                        }
                                        ?>
                                    </h5>
                                </div>
                                
                                <div class="row">
                                    <?php foreach ($day_classes as $class): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card class-card h-100" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; transition: all 0.3s;">
                                                <?php if ($class['capacity'] <= 5): ?>
                                                    <span class="badge class-badge" style="position: absolute; top: 10px; right: 10px; z-index: 10; background-color: #f7931e; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px;">Almost Full</span>
                                                <?php endif; ?>
                                                
                                                <div class="card-header" style="background-color: #f7931e; color: #fff; padding: 15px;">
                                                    <h5 class="mb-0" style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($class['name']); ?></h5>
                                                </div>
                                                
                                                <div class="card-body" style="padding: 20px;">
                                                    <?php 
                                                    // Fix image path handling
                                                    $image = isset($class['image']) ? $class['image'] : '';
                                                    if (!empty($image)) {
                                                        if (strpos($image, 'http') !== 0 && strpos($image, '/') !== 0 && strpos($image, 'assets/') !== 0) {
                                                            $class_image_url = '../../assets/images/Classes/' . $image;
                                                        } else {
                                                            $class_image_url = $image;
                                                        }
                                                    } else {
                                                        $class_image_url = '../../assets/images/Classes/yoga.jpg';
                                                    }
                                                    ?>
                                                    <div class="class-image mb-3">
                                                        <img src="<?php echo $class_image_url; ?>" alt="<?php echo htmlspecialchars($class['name']); ?>" class="img-fluid rounded">
                                                    </div>
                                                    
                                                    <p style="color: #333;"><?php echo htmlspecialchars($class['description']); ?></p>
                                                    
                                                    <div class="class-info">
                                                        <div class="class-info-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                                                            <span class="class-info-icon" style="margin-right: 8px; color: #f7931e;"><i class="fas fa-clock"></i></span>
                                                            <span style="color: #333;"><?php echo date('g:i A', strtotime($class['start_time'])); ?> - <?php echo date('g:i A', strtotime($class['end_time'])); ?></span>
                                                        </div>
                                                        
                                                        <div class="class-info-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                                                            <span class="class-info-icon" style="margin-right: 8px; color: #f7931e;"><i class="fas fa-map-marker-alt"></i></span>
                                                            <span style="color: #333;"><?php echo htmlspecialchars($class['location']); ?></span>
                                                        </div>
                                                        
                                                        <div class="class-info-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                                                            <span class="class-info-icon" style="margin-right: 8px; color: #f7931e;"><i class="fas fa-user"></i></span>
                                                            <span style="color: #333;"><?php echo htmlspecialchars($class['trainer_name']); ?></span>
                                                        </div>
                                                        
                                                        <div class="class-info-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                                                            <span class="class-info-icon" style="margin-right: 8px; color: #f7931e;"><i class="fas fa-users"></i></span>
                                                            <span style="color: #333;">
                                                                <?php 
                                                                // Get number of bookings for this class
                                                                $bookings = $db->fetchSingle("SELECT COUNT(*) as count FROM class_bookings WHERE class_id = ?", [$class['id']]);
                                                                $spots_taken = $bookings ? $bookings['count'] : 0;
                                                                echo $spots_taken . '/' . $class['capacity'] . ' spots taken';
                                                                ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card-footer bg-transparent" style="padding: 15px 20px; border-top: 1px solid #eee;">
                                                    <?php 
                                                    // Check if user has already booked this class
                                                    $is_booked = false;
                                                    foreach ($booked_classes as $booked) {
                                                        if ($booked['class_id'] == $class['id']) {
                                                            $is_booked = true;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    // Check if class is full
                                                    $is_full = $spots_taken >= $class['capacity'];
                                                    ?>
                                                    
                                                    <?php if ($is_booked): ?>
                                                        <button class="btn w-100" disabled style="background-color: #f7931e; color: #fff; border: none; padding: 10px 20px; border-radius: 4px;">
                                                            <i class="fas fa-check me-2"></i>Booked
                                                        </button>
                                                    <?php elseif ($is_full): ?>
                                                        <button class="btn w-100" disabled style="background-color: #f8f8f8; color: #666; border: 1px solid #ddd; padding: 10px 20px; border-radius: 4px;">
                                                            <i class="fas fa-users-slash me-2"></i>Class Full
                                                        </button>
                                                    <?php elseif ($has_membership): ?>
                                                        <form method="post">
                                                            <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                                            <button type="submit" name="book_class" class="btn w-100" style="background-color: #f7931e; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; transition: all 0.3s;">
                                                                <i class="fas fa-calendar-plus me-2"></i>Book Class
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="membership.php" class="btn w-100" style="background-color: transparent; color: #f7931e; border: 1px solid #f7931e; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; text-align: center; transition: all 0.3s;">
                                                            <i class="fas fa-lock me-2"></i>Get Membership to Book
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- My Bookings Tab -->
                    <div class="tab-pane fade" id="my-bookings" role="tabpanel" aria-labelledby="my-bookings-tab">
                        <?php if (empty($booked_classes)): ?>
                            <div class="alert" style="background-color: #f8f8f8; color: #000; border: 1px solid #ddd; border-radius: 5px; padding: 15px;">
                                <i class="fas fa-info-circle me-2" style="color: #f7931e;"></i> You haven't booked any classes yet. Browse available classes and book your spots!
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table" style="border-collapse: separate; border-spacing: 0; width: 100%; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                                    <thead>
                                        <tr style="background-color: #f7931e; color: #fff;">
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Class</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Date</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Time</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Location</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Trainer</th>
                                            <th style="padding: 15px; border-bottom: 1px solid #f7931e; font-weight: 500;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($booked_classes as $booking): ?>
                                            <tr style="border-bottom: 1px solid #eee;">
                                                <td style="padding: 12px 15px; color: #333;"><?php echo htmlspecialchars($booking['name']); ?></td>
                                                <td style="padding: 12px 15px; color: #333;">
                                                    <?php 
                                                    $date_obj = new DateTime($booking['class_date']);
                                                    $today = new DateTime('today');
                                                    $tomorrow = new DateTime('tomorrow');
                                                    
                                                    if ($date_obj->format('Y-m-d') === $today->format('Y-m-d')) {
                                                        echo '<span class="badge" style="background-color: #f7931e; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Today</span>';
                                                    } elseif ($date_obj->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                                                        echo '<span class="badge" style="background-color: #f7931e; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Tomorrow</span>';
                                                    } else {
                                                        echo $date_obj->format('m/d/Y');
                                                    }
                                                    ?>
                                                </td>
                                                <td style="padding: 12px 15px; color: #333;"><?php echo date('g:i A', strtotime($booking['start_time'])); ?> - <?php echo date('g:i A', strtotime($booking['end_time'])); ?></td>
                                                <td style="padding: 12px 15px; color: #333;"><?php echo htmlspecialchars($booking['location']); ?></td>
                                                <td style="padding: 12px 15px; color: #333;"><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                                                <td style="padding: 12px 15px;">
                                                    <form method="post" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <button type="submit" name="cancel_booking" class="btn btn-sm" style="background-color: transparent; color: #f7931e; border: 1px solid #f7931e; padding: 5px 10px; border-radius: 4px; font-size: 12px; transition: all 0.3s;">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </button>
                                                    </form>
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

    <!-- Class Details Modal -->
    <div class="modal fade" id="classDetailsModal" tabindex="-1" aria-labelledby="classDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background-color: #fff; color: #000; border: 2px solid #f7931e;">
                <div class="modal-header" style="background-color: #f7931e; color: #fff; border-bottom: 1px solid #f7931e;">
                    <h5 class="modal-title" id="classDetailsModalLabel" style="color: #fff;">Class Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: #fff; opacity: 0.8;"></button>
                </div>
                <div class="modal-body" id="classDetailsContent" style="color: #000;">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f7931e;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: transparent; color: #f7931e; border: 1px solid #f7931e; padding: 8px 16px; border-radius: 4px;">Close</button>
                    <button type="button" class="btn" id="bookClassBtn" style="background-color: #f7931e; color: #fff; border: none; padding: 8px 16px; border-radius: 4px;">Book Class</button>
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
        
        // Tab switching with proper orange styling
        const tabLinks = document.querySelectorAll('#classTab .nav-link');
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
        
        // Add hover effects to buttons
        const buttons = document.querySelectorAll('button[style*="background-color: #f7931e"], a[style*="color: #f7931e"]');
        buttons.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                if (this.style.backgroundColor === 'rgb(247, 147, 30)') {
                    this.style.backgroundColor = '#e8841a';
                } else if (this.style.color === 'rgb(247, 147, 30)') {
                    this.style.backgroundColor = 'rgba(247, 147, 30, 0.1)';
                }
            });
            
            btn.addEventListener('mouseleave', function() {
                if (this.style.backgroundColor === 'rgb(232, 132, 26)') {
                    this.style.backgroundColor = '#f7931e';
                } else if (this.style.backgroundColor === 'rgba(247, 147, 30, 0.1)') {
                    this.style.backgroundColor = 'transparent';
                }
            });
        });
    });
    </script>
</body>
</html>