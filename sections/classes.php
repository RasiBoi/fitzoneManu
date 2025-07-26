<?php
// Prevent direct access
if (!defined('FITZONE_APP')) {
    die('Direct access to this file is not allowed.');
}

// Get database connection
require_once 'includes/db_connect.php';
$db = getDb();

// First, get all classes from database and log them
$query = "SELECT * FROM fitness_classes ORDER BY id DESC";
$all_classes = $db->fetchAll($query);

// Process the classes to remove duplicates by name
$featured_classes = [];
$class_names = [];

// Add debug info to error log
error_log("Found " . count($all_classes) . " classes in total");

// Manual strict deduplication
if ($all_classes) {
    foreach ($all_classes as $class) {
        if (!isset($class['name']) || empty(trim($class['name']))) {
            continue; // Skip records without a valid name
        }
        
        // Don't skip any classes - we want all of them to show
        // Just transform the data and add to featured classes
        $featured_classes[] = $class;
    }
    
    // Transform the data to match the expected format
    foreach ($featured_classes as &$class) {
        $class['schedule'] = [$class['schedule_days'], $class['schedule_times']];
        
        // Fix image path by adding the proper directory prefix if not already included
        if (!empty($class['image'])) {
            // Only add prefix if image doesn't already have a path
            if (strpos($class['image'], 'http') !== 0 && 
                strpos($class['image'], 'assets/') !== 0 && 
                strpos($class['image'], '/') !== 0) {
                $class['image'] = 'assets/images/Classes/' . $class['image'];
            }
        } else {
            // Set a default image if none is provided
            $class['image'] = 'assets/images/Classes/yoga.jpg';
        }
    }
} else {
    // Fallback data in case of database error
    $featured_classes = [];
    error_log('Failed to fetch fitness classes from database');
}

// Log total unique classes found
error_log("Processing " . count($featured_classes) . " classes for display");
?>

<!-- Static class count to verify deduplication is working -->
<!-- Found <?php echo count($featured_classes); ?> unique classes -->

<section id="classes-section" class="classes-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 mx-auto text-center mb-5">
                <div class="section-heading mb-5">
                    <span class="section-subtitle">OUR CLASSES</span>
                    <h2 class="section-title text-black">Energize With Our <span class="fw-bold">Fitness Classes</span></h2>
                    <div class="section-separator"><span></span></div>
                    <p class="classes-intro">
                        Join our dynamic group fitness classes led by expert instructors designed to challenge, motivate, 
                        and transform your body. With options for all fitness levels, you'll find the perfect class to meet your goals.
                    </p>
                </div>
            </div>
        </div>

        <!-- Classes grid layout -->
        <div class="classes-grid">
            <?php if (count($featured_classes) > 0): ?>
                <div class="row">
                    <?php 
                    // Final display - ensure no duplicates were introduced
                    $displayed_names = []; 
                    
                    foreach ($featured_classes as $class): 
                        // Skip if somehow a duplicate made it through
                        $current_name = trim($class['name']);
                        if (in_array($current_name, $displayed_names)) continue;
                        $displayed_names[] = $current_name;
                    ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="class-card">
                                <div class="class-image">
                                    <img src="<?php echo $class['image']; ?>" alt="<?php echo $class['name']; ?>" class="img-fluid">
                                    <div class="class-duration">
                                        <i class="far fa-clock"></i> <?php echo $class['duration']; ?>
                                    </div>
                                    <div class="class-overlay">
                                        <div class="class-difficulty <?php echo strtolower($class['difficulty']); ?>">
                                            <?php echo $class['difficulty']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="class-content">
                                    <h3 class="class-title"><?php echo $class['name']; ?></h3>
                                    <div class="class-trainer">
                                        <i class="fas fa-user"></i> <?php echo $class['trainer']; ?>
                                    </div>
                                    <p class="class-description">
                                        <?php echo $class['description']; ?>
                                    </p>
                                    <div class="class-schedule">
                                        <div class="schedule-days">
                                            <i class="fas fa-calendar-week"></i>
                                            <span><?php echo $class['schedule'][0]; ?></span>
                                        </div>
                                        <div class="schedule-time">
                                            <i class="far fa-clock"></i>
                                            <span><?php echo $class['schedule'][1]; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <p>No classes found. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Background Elements for Visual Interest -->
    <!-- Removed colorful shapes for clean black and white design -->

    <!-- Inline CSS for grid layout -->
    <style>
    .classes-grid {
        margin-top: 20px;
    }

    .class-card {
        height: 100%;
        background-color: #ffffff;
        border: 1px solid #000000;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .class-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: #333333;
    }

    .class-image {
        position: relative;
        overflow: hidden;
    }

    .class-image img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.5s ease, filter 0.5s ease;
        filter: grayscale(100%);
    }

    .class-card:hover .class-image img {
        transform: scale(1.05);
    }

    .class-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .class-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #000000;
    }

    .class-trainer {
        font-size: 14px;
        color: #555555;
        margin-bottom: 12px;
    }

    .class-description {
        font-size: 14px;
        color: #666666;
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .class-schedule {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #000000;
    }

    .class-difficulty {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 4px;
        background-color: rgba(0, 0, 0, 0.7);
        color: #ffffff;
    }

    .class-duration {
        position: absolute;
        bottom: 10px;
        left: 10px;
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 4px;
        background-color: rgba(0, 0, 0, 0.7);
        color: #ffffff;
    }

    .beginner {
        background-color: #000000;
        border: 1px solid white;
    }

    .intermediate {
        background-color: #000000;
        border: 1px solid white;
    }

    .advanced {
        background-color: #000000;
        border: 1px solid white;
    }

    .all {
        background-color: #000000;
        border: 1px solid white;
    }

    @media (max-width: 991.98px) {
        .col-lg-3.col-md-6 {
            margin-bottom: 20px;
        }
    }
    
    /* Hide colorful shapes for black and white theme */
    .classes-shape {
        display: none;
    }
    
    /* Black and white specific styles */
    .classes-section {
        background-color: #f8f8f8;
        position: relative;
        padding: 80px 0;
    }
    
    .section-subtitle {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #000;
        margin-bottom: 10px;
        display: block;
    }
    
    .section-separator {
        width: 80px;
        height: 3px;
        background: #000;
        margin: 15px auto;
    }
    
    /* Class hover effects adjusted for B&W theme */
    .class-card:hover {
        background-color: #111;
        border-color: #444;
    }
    </style>
</section>