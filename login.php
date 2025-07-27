<?php
/**
 * FitZone Fitness Center
 * Login Page
 */

// Define constant to allow inclusion of necessary files
define('FITZONE_APP', true);

// Include configuration and helper files
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/authentication.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    initializeSession();
}

// Set current page for active menu highlighting
$current_page = 'login.php';

// Handle pending redirect after showing success message
if (isset($_SESSION['pending_redirect'])) {
    $redirect_url = $_SESSION['pending_redirect'];
    unset($_SESSION['pending_redirect']);
}

// Store redirect URL if provided
$redirect_to = '';
if (isset($_GET['redirect_to'])) {
    $redirect_to = sanitize($_GET['redirect_to']);
    // Store in session to preserve after POST
    $_SESSION['redirect_after_login'] = $redirect_to;
} elseif (isset($_SESSION['redirect_after_login'])) {
    $redirect_to = $_SESSION['redirect_after_login'];
}

// If user is already logged in, redirect to appropriate page
if (isLoggedIn()) {
    $userRole = getUserRole();
    
    // If there's a redirect URL, use it
    if (!empty($redirect_to)) {
        redirect($redirect_to);
    } else {
        // Otherwise redirect based on role
        if ($userRole === 'admin') {
            redirect('backend/admin/index.php');
        } elseif ($userRole === 'trainer') {
            redirect('backend/trainer/index.php');
        } else {
            redirect('backend/member/index.php');
        }
    }
}

// Process login form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $username = sanitize(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    if (empty($username)) {
        $error = 'Username or email is required.';
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } else {
        // Connect directly to database for login
        $db = getDb();
        
        // Check if input is email or username
        $field = isValidEmail($username) ? 'email' : 'username';
        
        // Get user data
        $user = $db->fetchSingle(
            "SELECT id, username, email, password, role, is_active FROM users WHERE $field = ?",
            [$username]
        );
        
        // Check if user exists and verify password
        if (!$user) {
            $error = 'Invalid username or password';
        } elseif (!$user['is_active']) {
            $error = 'Your account is not active. Please contact support.';
        } elseif (!verifyPassword($password, $user['password'])) {
            $error = 'Invalid username or password';
        } else {
            // Update last login time
            $db->query(
                "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$user['id']]
            );
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Set remember me cookie ONLY if requested
            if ($remember) {
                // Store the cookie securely 
                $selector = bin2hex(random_bytes(16));
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 days
                
                setcookie(
                    'remember_me',
                    $selector . ':' . $token,
                    [
                        'expires' => $expires,
                        'path' => '/',
                        'domain' => '',
                        'secure' => !empty($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
                
                // Also store in database for proper validation
                $db->query(
                    "INSERT INTO user_remember_tokens (user_id, selector, token_hash, expires) 
                     VALUES (?, ?, ?, ?)",
                    [
                        $user['id'],
                        $selector,
                        hash('sha256', $token),
                        date('Y-m-d H:i:s', $expires)
                    ]
                );
            } else {
                // If remember not checked, clear any existing remember cookies
                if (isset($_COOKIE['remember_me'])) {
                    setcookie(
                        'remember_me',
                        '',
                        [
                            'expires' => time() - 3600,
                            'path' => '/',
                            'domain' => '',
                            'secure' => !empty($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );
                }
                
                // Remove any existing tokens from database
                $db->query(
                    "DELETE FROM user_remember_tokens WHERE user_id = ?",
                    [$user['id']]
                );
            }
            
            // Log the action
            logAction('User login', "User {$user['username']} logged in", $user['id']);
            
            // Set success message
            setFlashMessage('success', 'Login successful! Welcome back.');
            
            // Store the redirect URL in session
            $_SESSION['pending_redirect'] = !empty($redirect_to) ? $redirect_to : (
                $user['role'] === 'admin' ? 'backend/admin/index.php' : (
                    $user['role'] === 'trainer' ? 'backend/trainer/index.php' : 'backend/member/index.php'
                )
            );
            
            // Clear the previous redirect session variable
            if (!empty($redirect_to)) {
                unset($_SESSION['redirect_after_login']);
            }

            // Instead of redirecting immediately, return to the page to show the message
            $success = 'success';
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitZone Fitness Center</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="shortcut icon" href="assets/images/favicon.png" type="image/x-icon">
    <style>
        body {
            background-color: #f0f0f0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.05);
            z-index: 0;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        .card {
            background-color: #ffffff;
            border: 1px solid rgba(221, 221, 221, 0.5);
            box-shadow: 
                0 10px 20px rgba(0, 0, 0, 0.12),
                0 5px 8px rgba(0, 0, 0, 0.06),
                inset 0 -5px 8px rgba(0, 0, 0, 0.02);
            border-radius: 15px;
            overflow: hidden;
            transform: perspective(1000px) translateZ(0);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .card:hover {
            transform: perspective(1000px) translateZ(10px);
            box-shadow: 
                0 15px 30px rgba(0, 0, 0, 0.15),
                0 8px 12px rgba(0, 0, 0, 0.08),
                inset 0 -8px 12px rgba(0, 0, 0, 0.03);
        }
        
        .card-body {
            color: #000000;
            border-radius: 15px;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
        }
        
        .form-label {
            color: #000000;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .input-group-text {
            background-color: #fff;
            border: 1px solid #ddd;
            color: #f7931e;
            border-radius: 8px 0 0 8px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            transform: translateZ(0);
        }
        
        .form-control {
            background-color: #ffffff;
            border: 1px solid #ddd;
            color: #000000;
            padding: 0.75rem 1rem;
            height: auto;
            transition: all 0.3s ease;
            border-radius: 0 8px 8px 0;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            transform: translateZ(0);
        }
        
        .form-control:focus {
            background-color: #ffffff;
            border-color: #f7931e;
            box-shadow: 0 0 0 0.2rem rgba(247, 147, 30, 0.25);
            color: #000000;
        }
        
        .form-control::placeholder {
            color: #888888;
        }
        
        .text-muted {
            color: #666666 !important;
        }
        
        /* Button styling */
        .btn-primary {
            background-color: #f7931e;
            border-color: #f7931e;
            color: #fff;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(247, 147, 30, 0.2);
            transform: translateZ(0);
        }        .btn-primary:hover, 
        .btn-primary:focus {
            background: #e07d0f !important;
            box-shadow: 
                0 8px 15px rgba(247, 147, 30, 0.3),
                0 4px 6px rgba(247, 147, 30, 0.2);
            transform: translateY(-2px) translateZ(10px);
        }
        
        .btn-outline-secondary {
            color: #666666;
            border-color: #000000;
            background-color: #ffffff;
        }
        
        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            color: #000000;
            background-color: #f0f0f0;
        }
        
        #togglePassword {
            background-color: #fff;
            color: #f7931e;
            border: 1px solid #ddd;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        #togglePassword:hover,
        #togglePassword:focus {
            color: #e07d0f;
            background-color: rgba(247, 147, 30, 0.1);
            border-color: #f7931e;
        }
        
        #header-popup-container {
            position: fixed !important;
            top: 20px !important;
            right: 20px !important;
            z-index: 99999 !important;
            pointer-events: none !important;
        }

        #header-popup-container .header-popup {
            position: relative !important;
            min-width: 300px !important;
            max-width: 380px !important;
            padding: 16px 20px !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            box-shadow: 
                0 8px 16px rgba(0, 0, 0, 0.15),
                0 3px 6px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-20px) translateZ(0) !important;
            opacity: 0 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            pointer-events: auto !important;
            margin-bottom: 10px !important;
        }
        
        #header-popup-container .header-popup.show {
            opacity: 1 !important;
            transform: translateY(0) translateZ(0) !important;
        }
        
        #header-popup-container .header-popup.success {
            background: #ffffff !important;
            color: #000000 !important;
            border: 2px solid #f7931e !important;
            box-shadow: 
                0 8px 16px rgba(247, 147, 30, 0.1),
                0 3px 6px rgba(247, 147, 30, 0.05) !important;
        }
        
        #header-popup-container .header-popup.error {
            background: #ffffff !important;
            color: #000000 !important;
            border: 2px solid #f7931e !important;
            box-shadow: 
                0 8px 16px rgba(247, 147, 30, 0.1),
                0 3px 6px rgba(247, 147, 30, 0.05) !important;
        }
        
        #header-popup-container .header-popup .fas {
            font-size: 20px !important;
            width: 24px !important;
            height: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #f7931e !important;
        }
        
        #header-popup-container .header-popup-icon {
            margin-right: 15px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 32px !important;
            height: 32px !important;
            background: rgba(247, 147, 30, 0.1) !important;
            border-radius: 50% !important;
            flex-shrink: 0 !important;
            box-shadow: 0 2px 4px rgba(247, 147, 30, 0.1) !important;
        }
        
        #header-popup-container .header-popup-message {
            font-size: 15px !important;
            font-weight: 500 !important;
            line-height: 1.5 !important;
            letter-spacing: 0.2px !important;
            color: #000000 !important;
        }
    </style>
</head>
<body>

<div id="header-popup-container">
    <!-- Popup notifications will be inserted here via JavaScript -->
</div>

<div class="login-container">
    <div class="card shadow border-0">
        <div class="card-body p-4 p-md-5 position-relative">
            
            <div class="text-center mb-4">
                <div class="logo-container mb-3">
                    <span style="font-size: 32px; font-weight: bold;">
                        <span style="color: #000;">Fit</span><span style="color: #f7931e;">Zone</span>
                    </span>
                </div>
                <h3 class="card-title mb-1" style="color: #000;">Welcome Back</h3>
                <p class="text-muted">Sign in to your account</p>
            </div>
            
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                            placeholder="Enter your username or email" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                            placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-end">
                        <a href="login.php" class="small" style="color: #000000ff; text-decoration: none; transition: all 0.3s ease;">Forgot Password?</a>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary py-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Sign In
                    </button>
                </div>
                
                <div class="text-center">
                    <p class="mb-0">Don't have an account? <a href="signup.php" class="fw-bold" style="color: #f7931e; text-decoration: none;">Sign Up</a></p>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
                <a href="index.php" class="btn btn-link" style="color: #000000ff; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-home me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    <?php if (!empty($error)): ?>
    // Display error popup
    showHeaderPopup('<?php echo addslashes($error); ?>', 'error');
    <?php endif; ?>
    
    <?php 
    $messages = getFlashMessages();
    foreach ($messages as $message): 
        $type = $message['type'] === 'success' ? 'success' : 'error';
    ?>
    // Display flash message popup
    showHeaderPopup('<?php echo addslashes($message['message']); ?>', '<?php echo $type; ?>');
    <?php endforeach; ?>
    
    // Function to show header popup messages
    function showHeaderPopup(message, type) {
        const container = document.getElementById('header-popup-container');
        const popup = document.createElement('div');
        popup.className = `header-popup ${type}`;
        
        // Set icon based on type
        let icon = '';
        if (type === 'success') {
            icon = '<i class="fas fa-check-circle header-popup-icon"></i>';
        } else if (type === 'error') {
            icon = '<i class="fas fa-exclamation-circle header-popup-icon"></i>';
        }
        
        popup.innerHTML = `
            ${icon}
            <div class="header-popup-message">${message}</div>
        `;
        
        container.appendChild(popup);
        
        // Show popup with a small delay
        setTimeout(() => {
            popup.classList.add('show');
            
            <?php if (isset($_SESSION['pending_redirect'])): ?>
            // If this is a success message and we have a pending redirect
            if (type === 'success') {
                // Wait for 1 second to show the message before redirecting
                setTimeout(() => {
                    window.location.href = '<?php echo isset($_SESSION['pending_redirect']) ? $_SESSION['pending_redirect'] : ''; ?>';
                }, 1000);
            }
            <?php endif; ?>

            // Auto-hide after 5 seconds (only for non-redirect messages)
            if (type !== 'success' || !<?php echo isset($_SESSION['pending_redirect']) ? 'true' : 'false'; ?>) {
                setTimeout(() => {
                    popup.classList.remove('show');
                    
                    // Remove from DOM after animation completes
                    setTimeout(() => {
                        container.removeChild(popup);
                    }, 500);
                }, 5000);
            }
        }, 100);
    }
});
</script>
</body>
</html>