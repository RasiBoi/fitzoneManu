<?php
/**
 * FitZone Fitness Center
 * Services Section
 */

// Prevent direct access
if (!defined('FITZONE_APP')) {
    die('Direct access to this file is not allowed.');
}
?>

<section id="services-section" class="services-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto text-center mb-5">
                <div class="section-heading">
                    <span class="section-subtitle">OUR OFFERINGS</span>
                    <h2 class="section-title">Premium <span class="text-black">Fitness Services</span></h2>
                    <div class="section-separator mx-auto"><span></span></div>
                    <p class="section-description">
                        Discover our comprehensive range of fitness services designed to help you achieve your health and wellness goals. Whether you're aiming for strength, endurance, flexibility, or overall well-being, we have the perfect program for you.
                    </p>
                </div>
            </div>
        </div>

        <div class="services-wrapper">
            <div class="row g-4">
                <!-- Service 1 -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon-wrapper">
                            <div class="service-icon">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                        </div>
                        <h3 class="service-title">Strength Training</h3>
                        <p class="service-description">
                            Increase strength, and boost metabolism with our strength training programs.
                        </p>
                        <div class="service-features">
                            
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>Expert Guidance</span>
                            </div>
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>Customized Plans</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon-wrapper">
                            <div class="service-icon">
                                <i class="fas fa-running"></i>
                            </div>
                        </div>
                        <h3 class="service-title">Cardio</h3>
                        <p class="service-description">
                            Improve endurance and burn calories with the cardio classes.
                        </p>
                        <div class="service-features">
                            
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>HIIT Training</span>
                            </div>
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>Heart Rate Zones</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon-wrapper">
                            <div class="service-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <h3 class="service-title">Group Classes</h3>
                        <p class="service-description">
                            Improve your workouts with our group fitness classes led by professional instructors.
                        </p>
                        <div class="service-features">
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>5+ Weekly Classes</span>
                            </div>
                            
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>Community Support</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service 4 -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="service-icon-wrapper">
                            <div class="service-icon">
                                <i class="fas fa-user-friends"></i>
                            </div>
                        </div>
                        <h3 class="service-title">Personal Training</h3>
                        <p class="service-description">
                            Improve your results with one-on-one coaching tailored to your specific fitness goals.
                        </p>
                        <div class="service-features">
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>Certified Trainers</span>
                            </div>
                           
                            <div class="service-feature">
                                <i class="fas fa-check"></i>
                                <span>Nutritional Advice</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Background shapes removed for clean black and white design -->
    
    <style>
        /* Black and white theme for services section */
        .services-section {
            background-color: #f8f8f8;
            color: #000;
        }
        
        .service-card {
            background-color: #fff;
            border: 1px solid #000;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .service-icon {
            background-color: #ffffff;
            color: #f7931e;
            border: 1px solid #f7931e;
        }
        
        .service-icon i {
            color: #f7931e !important;
            font-weight: bold;
        }
        
        .service-title {
            color: #000;
            font-weight: 700;
        }
        
        .service-description {
            color: #333;
            font-weight: 500;
        }
        
        .service-feature i {
            color: #000;
            font-weight: bold;
        }
        
        .service-feature span {
            color: #333;
            font-weight: 500;
        }
        
        /* Hide the background shapes */
        .services-shape-1,
        .services-shape-2 {
            display: none;
        }
        
        .text-black {
            color: #000;
        }
    </style>
</section>