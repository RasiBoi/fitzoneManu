<?php
/**
 * FitZone Fitness Center
 * Hero Section
 */

// Prevent direct access
if (!defined('FITZONE_APP')) {
    die('Direct access to this file is not allowed.');
}
?>

<section class="hero-section" id="hero-section" style="background: #ffffff !important;">
    <div class="hero-overlay" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.85)) !important;"></div>
    <div class="hero-particles" id="hero-particles" style="background: transparent !important;"></div>
    
    <div class="container position-relative">
        <div class="row align-items-center hero-content" style="min-height: 550px;">
            <div class="col-lg-6 hero-text-area">
                <div class="hero-subtitle" style="color: #000000; font-weight: 600;">PHYSIQUE IS EVERYTHING</div>
                <h1 class="hero-title" style="color: #000000;">
                    <span class="text-black" style="font-weight: 700;">Transform</span> Your Body.
                    <span class="d-block" style="color: #000000; font-weight: 700;">Energize Your Life.</span>
                </h1>
                <p class="hero-description" style="color: #333333; font-weight: 500;">
                    Your ultimate fitness destination. FitZone provides state-of-the-art equipment, expert trainers, and a powerful community dedicated to crushing your goals.
                </p>
                <div class="hero-buttons">
                    <a href="membership.php" class="btn btn-dark btn-lg hero-btn">
                        <span>JOIN NOW</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        
            <div class="col-lg-6 hero-image-area">
                <div class="hero-background-image">
                    <!-- Empty div that will have the background image applied via CSS -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Custom styles for black and white hero section -->
<style>
    /* Updated hero section with background image on the right side */
    .hero-image-area {
        position: relative !important;
        height: 500px !important;
    }
    
    .hero-background-image {
        position: absolute !important;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        background-image: url('assets/images/hero-new.jpg') !important;
        background-position: center right !important;
        background-size: cover !important;
        background-repeat: no-repeat !important;
        filter: grayscale(100%) !important;
        border-radius: 10px !important;
    }
    
    .text-black {
        color: #000 !important;
        background: none !important;
        -webkit-background-clip: initial !important;
        -webkit-text-fill-color: #000 !important;
        font-weight: bold !important;
    }
    
    /* Remove shape animations */
    .hero-shape-1, .hero-shape-2 {
        display: none !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991px) {
        .hero-background-image {
            height: 400px !important;
            margin-top: 30px !important;
        }
    }
</style>

<!-- Particles.js script removed to maintain black and white theme -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Particles disabled for black and white theme
    console.log("Particles disabled for black and white theme");
    
    // Clean up any existing particles
    const heroParticles = document.getElementById('hero-particles');
    if (heroParticles) {
        heroParticles.style.background = 'transparent';
        heroParticles.innerHTML = '';
    }
    
    // Remove any data-aos attributes
    const elements = document.querySelectorAll('[data-aos]');
    elements.forEach(el => {
        el.removeAttribute('data-aos');
        el.removeAttribute('data-aos-duration');
    });
});
</script>