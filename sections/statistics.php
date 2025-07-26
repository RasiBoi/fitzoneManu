<?php
/**
 * FitZone Fitness Center
 * Statistics Section
 */

// Prevent direct access
if (!defined('FITZONE_APP')) {
    die('Direct access to this file is not allowed.');
}

// Hardcoded statistics values
$stats = [
    'trainer_count' => 50,
    'active_clients' => 600,
    'cert_count' => 30,
    'total_experience' => 5
];
?>

<!-- Trainer Statistics Section -->
<div class="trainer-stats-section counter-section">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-number">
                <span class="counter-number" data-target="<?php echo $stats['trainer_count']; ?>">0</span>+
            </div>
            <div class="stat-label">Expert Trainers</div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number">
                <span class="counter-number" data-target="<?php echo $stats['active_clients']; ?>">0</span>+
            </div>
            <div class="stat-label">Happy Clients</div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-certificate"></i>
            </div>
            <div class="stat-number">
                <span class="counter-number" data-target="<?php echo $stats['cert_count']; ?>">0</span>+
            </div>
            <div class="stat-label">Certifications</div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-number">
                <span class="counter-number" data-target="<?php echo $stats['total_experience']; ?>">0</span>+
            </div>
            <div class="stat-label">Years Experience</div>
        </div>
    </div>
</div>

<style>
/* Statistics Section */
.trainer-stats-section {
    margin-top: -80px;
    margin-bottom: 50px;
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: center; /* Center the statistics container horizontally */
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    padding: 40px;
    background: #ffffff;
    border: 2px solid #000000;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    max-width: 1400px; /* Set a max-width to ensure proper centering */
    width: 100%; /* Ensure it takes full width up to max-width */
}

.stat-item {
    text-align: center;
    padding: 20px;
    position: relative;
    border-right: 1px solid rgba(0, 0, 0, 0.1);
}

.stat-item::after {
    display: none;
}

.stat-item:last-child {
    border-right: none;
}

.stat-icon {
    font-size: 28px;
    color: #000000;
    margin-bottom: 15px;
    text-shadow: none;
}

.stat-number {
    font-size: 42px;
    font-weight: 800;
    color: #000000;
    line-height: 1;
    margin-bottom: 15px;
    text-shadow: none;
}

.stat-label {
    font-size: 16px;
    color: #000000;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 600;
    text-shadow: none;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
        padding: 30px;
    }
    
    .stat-item:nth-child(2) {
        border-right: none;
    }
    
    .stat-item:nth-child(1), .stat-item:nth-child(2) {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding-bottom: 30px;
        margin-bottom: 10px;
    }
}

@media (max-width: 575.98px) {
    .stats-container {
        grid-template-columns: 1fr;
        padding: 25px;
    }
    
    .stat-item {
        border-right: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    
    .stat-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .stat-number {
        font-size: 36px;
    }
    
    .stat-icon {
        font-size: 24px;
    }
}
</style>

<!-- Counter Up Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.counter-number');
    let started = false;

    function startCount(counter) {
        const target = parseInt(counter.getAttribute('data-target'), 10);
        const increment = target / 100; // Smoother increment
        let current = 0;

        const updateCounter = () => {
            current += increment;
            counter.textContent = Math.floor(Math.min(current, target));
            if (current < target) {
                requestAnimationFrame(updateCounter);
            }
        };

        updateCounter();
    }

    const observerCallback = (entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !started) {
                started = true;
                // Add a small delay before starting the count
                setTimeout(() => {
                    counters.forEach(counter => startCount(counter));
                }, 100);
            }
        });
    };

    // Create Intersection Observer
    const observer = new IntersectionObserver(observerCallback, { threshold: 0.5 });

    // Observe the counter section
    const counterSection = document.querySelector('.counter-section');
    if (counterSection) {
        observer.observe(counterSection);
    }
});
</script>