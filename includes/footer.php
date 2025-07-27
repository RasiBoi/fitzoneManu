<?php

// Prevent direct script access
if (!defined('FITZONE_APP')) {
    exit('Direct script access denied.');
}

// Get current year for copyright
$currentYear = date('Y');
?>

        </main> <!-- End of main content -->

        <!-- Footer -->
        <footer class="footer bg-light text-dark mt-5 pt-5" style="border-top: 4px solid #000;">
            <div class="container">
                <div class="row">
                    <!-- About Column -->
                    <div class="col-lg-4 mb-4">
                        <h4 class="mb-3" style="color: #f7931e; font-weight: bold;">About</h4>
                        <div class="footer-logo mb-3">
                            <span class="logo-fit">Fit</span><span class="logo-zone" style="color: #f7931e;">Zone</span>
                        </div>
                        <p class="text-dark">
                            FitZone is Kurunegala's premier fitness center, offering state-of-the-art equipment, guidance from expert trainers, and a diverse range of fitness programs—all designed to help you achieve your health and wellness goals.
                        </p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-dark me-3"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-dark me-3"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-dark me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-dark me-3"><i class="fab fa-youtube"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    
                    <!-- Quick Links Column - Added padding-left for more right alignment -->
                    <div class="col-lg-4 col-md-6 mb-4" style="padding-left: 140px;">
                        <h5 class="mb-3" style="color: #f7931e; font-weight: bold;">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none text-dark">Home</a></li>
                            <li class="mb-2"><a href="<?php echo SITE_URL; ?>about.php" class="text-decoration-none text-dark">About Us</a></li>
                            <li class="mb-2"><a href="<?php echo SITE_URL; ?>services/index.php" class="text-decoration-none text-dark">Services</a></li>
                            <li class="mb-2"><a href="<?php echo SITE_URL; ?>membership/index.php" class="text-decoration-none text-dark">Membership</a></li>
                            <li class="mb-2"><a href="<?php echo SITE_URL; ?>blog/index.php" class="text-decoration-none text-dark">Blog</a></li>
                            <li class="mb-2"><a href="<?php echo SITE_URL; ?>contact.php" class="text-decoration-none text-dark">Contact</a></li>
                        </ul>
                    </div>
                
                    
                    <!-- Contact Info Column -->
                    <div class="col-lg-4 mb-4">
                        <h5 class="mb-3" style="color: #f7931e; font-weight: bold;">Contact Info</h5>
                        <ul class="list-unstyled footer-contact">
                            <li class="d-flex mb-3">
                                <i class="fas fa-map-marker-alt mt-1 me-3" style="color: #f7931e;"></i>
                                <span>No. 123, Main Street,<br>Kurunegala, Sri Lanka</span>
                            </li>
                            <li class="d-flex mb-3">
                                <i class="fas fa-phone-alt mt-1 me-3" style="color: #f7931e;"></i>
                                <span>+94 76 123 4567</span>
                            </li>
                            <li class="d-flex mb-3">
                                <i class="fas fa-envelope mt-1 me-3" style="color: #F48915;"></i>
                                <span>info@fitzone.com</span>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-clock mt-1 me-3" style="color: #F48915;"></i>
                                <div>
                                    <p class="mb-1">Monday - Friday: 6:00 AM - 10:00 PM</p>
                                    <p class="mb-1">Saturday: 7:00 AM - 8:00 PM</p>
                                    <p class="mb-0">Sunday: 8:00 AM - 6:00 PM</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <hr class="mt-2 mb-2 border-dark">
                
                <!-- Bottom Footer with Centered Copyright -->
                <div class="bottom-footer pb-4 text-center">
                    <p class="mb-0">&copy; <?php echo $currentYear; ?> FitZone Fitness Center. All Rights Reserved.</p>
                </div>
            </div>
            
            <style>
                /* Footer Theme with Orange Accents */
                .footer {
                    background-color: #f8f8f8 !important;
                    color: #000 !important;
                    position: relative;
                }
                
                .footer::before {
                    content: '';
                    position: absolute;
                    top: -4px;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background-color: #000;
                }
                
                .footer a {
                    color: #333 !important;
                    transition: all 0.3s ease;
                }
                
                .footer a:hover {
                    color: #f7931e !important;
                    text-decoration: none !important;
                }
                
                .footer h4, .footer h5 {
                    color: #f7931e !important;
                    font-weight: bold !important;
                }
                
                .social-links a {
                    background-color: transparent !important;
                    border: 1px solid #f7931e !important;
                    color: #f7931e !important;
                    width: 36px !important;
                    height: 36px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    border-radius: 50% !important;
                    margin-right: 10px !important;
                    transition: all 0.3s ease !important;
                }
                
                .social-links a:hover {
                    background-color: #f7931e !important;
                    color: #fff !important;
                    transform: translateY(-3px);
                }
                
                .footer .contact-detail-item i {
                    color: #f7931e !important;
                }
                
                .footer .footer-logo {
                    font-size: 24px;
                    font-weight: bold;
                    letter-spacing: 0.5px;
                }

                .footer .logo-fit {
                    color: #000;
                }

                .footer .logo-zone {
                    color: #f7931e;
                }

                .footer .contact-info h4 {
                    font-weight: 600 !important;
                    margin-bottom: 5px !important;
                    color: #000 !important;
                }

                .footer .contact-info p {
                    color: #333 !important;
                    margin-bottom: 0 !important;
                }

                .footer hr {
                    border-color: #f7931e !important;
                    opacity: 0.1;
                }

                .footer .bottom-footer {
                    color: #333 !important;
                }

                .footer .bottom-footer p {
                    color: #333 !important;
                }
            </style>
        </footer>
        
        <!-- Back to Top Button -->
        <button id="back-to-top" class="btn rounded-circle" style="background-color: #f7931e; color: #fff;">
            <i class="fas fa-arrow-up"></i>
        </button>
        
        <style>
        #back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            z-index: 999;
            transition: all 0.3s ease;
        }
        
        #back-to-top:hover {
            transform: translateY(-5px);
            background-color: #e07d0f !important;
        }
        
        #back-to-top.show {
            display: block;
        }
        </style>
        
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- jQuery (required for some custom functionality) -->
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        
        <!-- Custom JavaScript -->
        <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
        
        <?php if (isset($extra_js)): ?>
            <!-- Page-specific JavaScript -->
            <?php echo $extra_js; ?>
        <?php endif; ?>
        
        <script>
            // Back to top button functionality
            $(document).ready(function() {
                var btn = $('#back-to-top');
                
                $(window).scroll(function() {
                    if ($(window).scrollTop() > 300) {
                        btn.addClass('show');
                    } else {
                        btn.removeClass('show');
                    }
                });
                
                btn.on('click', function(e) {
                    e.preventDefault();
                    $('html, body').animate({scrollTop:0}, '300');
                });
                
                // Improve text visibility throughout the site
                function improveTextVisibility() {
                    // Make section headings more visible
                    $('.section-title').css({
                        'color': '#000000',
                        'font-weight': '800',
                        'letter-spacing': '0.5px'
                    });
                    
                    $('.section-subtitle').css({
                        'color': '#000000',
                        'font-weight': '700',
                        'letter-spacing': '2px'
                    });
                    
                    // Enhance card text
                    $('.card-title, .service-title, .class-title, .testimonial-title').css({
                        'color': '#000000',
                        'font-weight': '700'
                    });
                    
                    // Enhance descriptions
                    $('.card-text, .service-description, .class-description, .testimonial-text').css({
                        'color': '#333333',
                        'font-weight': '500'
                    });
                    
                    // Fix testimonials text
                    $('.testimonial-card').css('border', '1px solid #000');
                }
                
                // Run once page is fully loaded
                $(document).ready(function() {
                    improveTextVisibility();
                });
                
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
                
                // Search functionality is now handled in main.js
            });
            
            // Current server time display (if needed)
            function updateServerTime() {
                const timeElement = document.getElementById('server-time');
                if (timeElement) {
                    const now = new Date();
                    const options = { 
                        year: 'numeric', 
                        month: '2-digit', 
                        day: '2-digit',
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit',
                        hour12: false
                    };
                    timeElement.textContent = now.toLocaleString('en-US', options).replace(',', '');
                    setTimeout(updateServerTime, 1000);
                }
            }
            // Uncomment to enable server time display
            // updateServerTime();
        </script>
    </body>
</html>