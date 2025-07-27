<?php
/**
 * FitZone Fitness Center
 * Trainers Section with Creative Design
 * 
 */

// Prevent direct access
if (!defined('FITZONE_APP')) {
    die('Direct access to this file is not allowed.');
}

// Current timestamp and user from parameters
$current_datetime = '2025-04-03 04:42:10';
$current_user = 'kaveeshawi';

// Fetch trainers data
try {
    $db = getDb();
    
    // Get all active trainers ordered by experience
    $query = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as name 
              FROM trainers t 
              JOIN users u ON t.user_id = u.id 
              WHERE t.is_active = 1 
              ORDER BY t.experience DESC";
    
    $trainers = $db->fetchAll($query);
    $trainerCount = count($trainers);
    $needSlider = $trainerCount > 3;
    
    // Get statistics
    $statsQuery = "SELECT 
                    (SELECT COUNT(*) FROM trainers WHERE is_active = 1) as trainer_count,
                    (SELECT COUNT(*) FROM members WHERE status = 'active') as active_clients,
                    (SELECT COUNT(DISTINCT certification) FROM trainers WHERE is_active = 1) as cert_count,
                    (SELECT SUM(experience) FROM trainers WHERE is_active = 1) as total_experience";
    
    $stats = $db->fetchSingle($statsQuery);
} catch (Exception $e) {
    error_log("Database error in trainers.php: " . $e->getMessage());
    $trainers = [];
    $needSlider = false;
}
?>

<section id="trainers" class="trainers-section">
    <div class="container">
        <!-- Section Header -->
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <div class="section-heading">
                    <span class="section-subtitle">INSTRUCTORS</span>
                    <h2 class="section-title">Here is Our <span class="text-black">Professional Trainers</span></h2>
                    <div class="section-separator mx-auto"><span></span></div>
                    <p class="trainers-intro">
                        Work alongside our team of certified trainers, who bring extensive experience and specialized knowledge to every session. Whether your focus is on building strength, achieving weight loss, or enhancing flexibility, our experts are dedicated to guiding you toward your fitness aspirations.
                    </p>
                </div>
            </div>
        </div>

        <!-- Trainers Display -->
        <?php if (!empty($trainers)): ?>
            <div class="trainer-display-section">
                <!-- Black and White Grid Layout -->
                <div class="trainer-grid">
                    <?php foreach ($trainers as $trainer): ?>
                    <div class="trainer-card">
                        <div class="trainer-card-inner">
                            <div class="trainer-image-container">
                                <div class="trainer-image-wrapper">
                                    <img src="<?php echo htmlspecialchars($trainer['image']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>" class="trainer-image">
                                </div>
                            </div>
                            <div class="trainer-content">
                                <h3 class="trainer-name"><?php echo htmlspecialchars($trainer['name']); ?></h3>
                                <div class="trainer-specialization"><?php echo htmlspecialchars($trainer['specialization']); ?></div>
                                <div class="trainer-experience"><?php echo (int)$trainer['experience']; ?> Years Experience</div>
                                <p class="trainer-bio"><?php echo htmlspecialchars($trainer['bio']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>   
        <?php endif; ?>
    </div>
    
    <!-- Background Elements -->
    <div class="trainer-shape-1"></div>
    <div class="trainer-shape-2"></div>
</section>

<style>
/* Main Section Styling */
.trainers-section {
    position: relative;
    padding: 80px 0;
    overflow: hidden;
    background-color: #ffffff;
    color: #000000;
}

.trainers-intro {
    margin-bottom: 40px;
    color: #000000;
}

/* Black and white decorative elements */
.trainer-shape-1, .trainer-shape-2 {
    position: absolute;
    border-radius: 0;
    z-index: 0;
    opacity: 0.05;
}

.trainer-shape-1 {
    top: -50px;
    right: -50px;
    width: 300px;
    height: 300px;
    background: #000000;
    transform: rotate(45deg);
}

.trainer-shape-2 {
    bottom: -50px;
    left: -50px;
    width: 400px;
    height: 400px;
    background: #000000;
    transform: rotate(15deg);
}

/* Trainer Display Section */
.trainer-display-section {
    position: relative;
    z-index: 1;
}

/* Trainer Grid */
.trainer-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Trainer Card - Black and white design */
.trainer-card {
    position: relative;
    overflow: hidden;
    height: 500px; /* Fixed height for all trainer cards */
    transition: transform 0.3s ease;
}

.trainer-card:hover {
    transform: translateY(-10px);
}

.trainer-card-inner {
    background: #ffffff;
    border: 1px solid #000000;
    padding: 30px;
    position: relative;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    border-radius: 15px;
}

.trainer-card:hover .trainer-card-inner {
    box-shadow: 0 15px 30px rgba(247, 147, 30, 0.2);
    border-color: #f7931e;
}

/* Trainer Image */
.trainer-image-container {
    position: relative;
    text-align: center;
    margin-bottom: 25px;
    flex-shrink: 0; /* Prevent image from shrinking */
}

.trainer-image-wrapper {
    position: relative;
    width: 180px;
    height: 180px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 12px;
}

.trainer-image {
    width: 180px;
    height: 180px;
    object-fit: cover;
    position: relative;
    z-index: 2;
    filter: none;
    transition: all 0.4s ease;
}

.trainer-card:hover .trainer-image {
    transform: scale(1.05);
    filter: none !important;
}

/* Trainer Content */
.trainer-content {
    text-align: center;
    display: flex;
    flex-direction: column;
    flex: 1;
    padding-top: 15px;
    position: relative;
}

.trainer-content:before {
    content: "";
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 2px;
    background-color: #f7931e;
}

.trainer-name {
    font-size: 22px;
    font-weight: 700;
    color: #000000;
    margin-bottom: 8px;
    letter-spacing: 1px;
}

.trainer-specialization {
    color: #ffffff;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    display: inline-block;
    padding: 5px 15px;
    background: #f7931e;
    border: none;
    box-shadow: 0 2px 4px rgba(247, 147, 30, 0.2);
    border-radius: 8px;
}

/* Experience now below specialization */
.trainer-experience {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 15px;
    color: #f7931e;
    display: block;
}

.trainer-bio {
    color: #333333;
    font-size: 14px;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: auto; /* Push bio to the bottom of available space */
}

/* Responsive Styles */
@media (max-width: 991.98px) {
    .trainer-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .trainer-card {
        height: 480px;
    }
    
    .section-title {
        font-size: 2.2rem;
    }
}

@media (max-width: 767.98px) {
    .trainer-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .trainer-image-wrapper,
    .trainer-image {
        width: 160px;
        height: 160px;
    }
    
    .trainer-card-inner {
        padding: 25px;
    }
    
    .trainer-card {
        height: auto;
        max-width: 400px;
        margin: 0 auto;
    }
}

@media (max-width: 575.98px) {
    .trainers-section {
        padding: 60px 0;
    }
    
    .trainer-image-wrapper,
    .trainer-image {
        width: 140px;
        height: 140px;
    }
}
</style>