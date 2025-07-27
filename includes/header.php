<?php
 // Prevent direct script access
if (!defined('FITZONE_APP')) {
    exit('Direct script access denied.');
}

// Initialize current_page if not set
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-url" content="<?php echo SITE_URL; ?>">
    <title>Fitzone Fitness Center</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>assets/images/favicon.png" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/home.css">
    <!-- Black and White Theme Override -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/black-white-override.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/black-white-navbar.css">
    <!-- Improved Text Visibility for Black and White Theme -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/improved-visibility.css">

    <!-- Swiper library -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    
    <!-- Theme Override JavaScript -->
    <script src="<?php echo SITE_URL; ?>assets/js/theme-override.js"></script>
    
    <!-- Inline styles for navbar visibility -->
    <style>
        /* Enhanced navbar styling */
        #custom-navbar {
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            transition: all 0.3s ease;
        }

        /* Navigation link styling */
        #custom-navbar .nav-link {
            position: relative;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            color: #000000 !important;
            font-weight: 600 !important;
        }

        #custom-navbar .nav-link:hover {
            color: #f7931e !important;
            transform: translateY(-2px);
        }

        #custom-navbar .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: #f7931e;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        #custom-navbar .nav-link:hover::after {
            width: 100%;
        }

        #custom-navbar .nav-link.active {
            color: #f7931e !important;
        }

        #custom-navbar .nav-link.active::after {
            width: 100%;
            background-color: #f7931e;
        }

        /* Logo styling */
        .navbar-brand {
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-decoration: none;
            padding: 5px 0;
        }

        .logo-text {
            color: #000;
            font-family: 'Arial', sans-serif;
            margin-right: 2px;
        }

        .logo-text-zone {
            color: #f7931e;
            font-family: 'Arial', sans-serif;
        }

        .logo-container:hover .logo-text,
        .logo-container:hover .logo-text-zone {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        /* Logo hover effect */
        .navbar-brand:hover .logo-img {
            transform: scale(1.05);
        }

        /* Improved navigation links */
        #custom-navbar .nav-link, 
        #custom-navbar .navbar-nav .nav-link,
        .navbar-nav .nav-link, 
        #navbarMain .nav-link,
        .nav-item a,
        .nav-link,
        #custom-navbar a {
            color: #000000 !important;
            font-weight: 600 !important;
            padding: 0.8rem 1.2rem;
            position: relative;
            transition: color 0.3s ease;
        }

        /* Hover effect for nav links */
        #custom-navbar .nav-link:hover {
            color: #f7931e !important;
        }

        /* Active link indicator */
        #custom-navbar .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 2px;
            background-color: #f7931e;
        }

        /* Enhanced search icon */
        .search-icon, .search-icon i {
            color: #000000 !important;
            transition: color 0.3s ease;
        }
        
        .search-icon:hover, .search-icon:hover i {
            color: #f7931e !important;
        }

        /* Enhanced login button styling */
        .btn-login {
            color: #000000 !important;
            border: 2px solid #000000 !important;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
            background-color: transparent !important;
        }

        .btn-login:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background-color: #f7931e;
            transition: all 0.4s ease;
            z-index: -1;
        }

        .btn-login:hover {
            color: #ffffff !important;
            border-color: #f7931e !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(247, 147, 30, 0.3);
        }

        .btn-login:hover:before {
            left: 0;
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(247, 147, 30, 0.3);
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .navbar-brand {
                padding: 0.5rem 0;
            }
            
            #custom-navbar .nav-link {
                padding: 0.5rem 1rem;
            }
            
            .btn-login {
                margin-top: 0.5rem;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>


<body>
    <!-- Main Navigation - New Design -->
    <nav id="custom-navbar" class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
        <div class="container">
            <!-- Logo on the left -->
            <a class="navbar-brand py-2" href="<?php echo SITE_URL; ?>">
                <div class="logo-container">
                    <span class="logo-text">Fit</span>
                    <span class="logo-text-zone">Zone</span>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                    aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Navigation Links -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                           href="<?php echo SITE_URL; ?>#hero-section" style="color: #000000 !important; font-weight: 600;">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'about.php' ? 'active' : ''; ?>" 
                           href="<?php echo SITE_URL; ?>#about-section" style="color: #000000 !important; font-weight: 600;">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'services') !== false ? 'active' : ''; ?>" 
                           href="<?php echo SITE_URL; ?>#services-section" style="color: #000000 !important; font-weight: 600;">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'classes.php' ? 'active' : ''; ?>" 
                           href="<?php echo SITE_URL; ?>#classes-section" style="color: #000000 !important; font-weight: 600;">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'membership/index.php' || $current_page == 'membership.php' ? 'active' : ''; ?>" 
                           href="<?php echo SITE_URL; ?>#membership-section" style="color: #000000 !important; font-weight: 600;">Membership</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>" 
                           href="<?php echo SITE_URL; ?>#cta-section" style="color: #000000 !important; font-weight: 600;">Contact</a>
                    </li>
                </ul>
                
                <!-- Search and Login on the right -->
                <div class="d-flex align-items-center">
                    <!-- Search Icon and Dropdown -->
                    <div class="search-container">
                        <div class="search-icon" id="search-toggle" style="color: #000000 !important;">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="search-dropdown" id="search-dropdown">
                            <form class="search-form" id="search-form" action="<?php echo SITE_URL; ?>includes/search.php" method="get">
                                <input class="search-input" type="search" placeholder="Search..." aria-label="Search" name="q" id="search-input">
                                <button class="search-submit" type="submit">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </form>
                            <div class="search-results" id="search-results"></div>
                        </div>
                    </div>
                    
                    <!-- Login Button -->
                    <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-login" style="color: #000000 !important; border-color: #000000 !important;">Log In</a>
                </div>
            </div>
        </div>
    </nav>

<!-- Add search styles -->
<style>
.search-container {
    position: relative;
    margin-right: 15px;
}

.search-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 2px solid #000000;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #000;
}

.search-icon:hover {
    background-color: #f7931e;
    border-color: #f7931e;
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(247, 147, 30, 0.3);
}

.search-icon:hover i {
    color: #ffffff !important;
}

.search-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 320px;
    background-color: #ffffff;
    padding: 15px;
    border-radius: 12px;
    border: 2px solid #000000;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    visibility: hidden;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
}

.search-dropdown.active {
    visibility: visible;
    opacity: 1;
    transform: translateY(5px);
}

.search-form {
    display: flex;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border: 2px solid #000000;
    border-radius: 25px;
    font-size: 0.95rem;
    background-color: #ffffff;
    transition: all 0.3s ease;
    outline: none;
}

.search-input:focus {
    border-color: #f7931e;
    box-shadow: 0 0 0 3px rgba(247, 147, 30, 0.15);
}

.search-submit {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: #000000;
    color: white;
    border: 2px solid #000000;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
}

.search-submit:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: #f7931e;
    transition: all 0.4s ease;
    z-index: 1;
}

.search-submit:hover {
    border-color: #f7931e;
    box-shadow: 0 3px 8px rgba(247, 147, 30, 0.3);
}

.search-submit:hover:before {
    left: 0;
}

.search-submit i {
    position: relative;
    z-index: 2;
}

.search-results {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 10px;
    display: none;
}

.search-result-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-title {
    font-weight: 600;
    margin-bottom: 3px;
    color: #212529;
}

.search-result-description {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
}

.search-result-badge {
    display: inline-block;
    padding: 2px 6px;
    margin-bottom: 5px;
    font-size: 0.7rem;
    font-weight: 500;
    border-radius: 3px;
}

.search-empty-result {
    padding: 15px 10px;
    text-align: center;
    color: #6c757d;
}

.search-highlight {
    background-color: #ffeb3b;
    padding: 0 2px;
    border-radius: 2px;
}

.search-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 15px;
    background-color: #28a745;
    color: white;
    border-radius: 4px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
    z-index: 1050;
    transform: translateX(150%);
    opacity: 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.search-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.search-notification.error {
    background-color: #dc3545;
}

.search-notification i {
    margin-right: 8px;
}

.search-info {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 10px 0 5px;
    display: flex;
    justify-content: space-between;
}

/* Loading indicator */
.search-loading {
    display: none;
    text-align: center;
    padding: 10px;
}

.search-loading .spinner-border {
    width: 1.5rem;
    height: 1.5rem;
    border-width: 0.2em;
}

@media (max-width: 576px) {
    .search-dropdown {
        width: 280px;
    }
}
</style>