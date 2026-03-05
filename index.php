<?php
require_once 'db.php';

$message = '';
$is_login = true;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    if ($action === 'signup') {
        // Signup logic
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $message = '<div class="alert alert-error">All fields are required!</div>';
            $is_login = false;
        } else {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $message = '<div class="alert alert-error">Email or username already exists!</div>';
                $is_login = false;
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
                    $message = '<div class="alert alert-success">Account created successfully! Please login.</div>';
                    $is_login = true;
                } else {
                    $message = '<div class="alert alert-error">Registration failed. Please try again.</div>';
                    $is_login = false;
                }
            }
        }
    } elseif ($action === 'login') {
        // Login logic
        if (empty($username) || empty($password)) {
            $message = '<div class="alert alert-error">Username and password are required!</div>';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                header('Location: home.php');
                exit;
            } else {
                $message = '<div class="alert alert-error">Invalid credentials!</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeonStay | Login & Signup</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Animated Background -->
    <div class="neon-bg"></div>
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">
                <i class="fas fa-home"></i> NeonStay
            </h1>
            
            <?php echo $message; ?>
            
            <!-- Toggle Buttons -->
            <div class="text-center mb-4">
                <button class="btn-neon-outline <?php echo $is_login ? 'active' : ''; ?>" onclick="showLogin()">Login</button>
                <button class="btn-neon-outline <?php echo !$is_login ? 'active' : ''; ?>" onclick="showSignup()">Sign Up</button>
            </div>
            
            <!-- Login Form -->
            <form id="loginForm" method="POST" action="" style="display: <?php echo $is_login ? 'block' : 'none'; ?>;">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Username or Email</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username or email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn-neon w-100">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div class="text-center mt-3">
                    <a href="#" style="color: var(--neon-primary); text-decoration: none;">Forgot Password?</a>
                </div>
            </form>
            
            <!-- Signup Form -->
            <form id="signupForm" method="POST" action="" style="display: <?php echo !$is_login ? 'block' : 'none'; ?>;">
                <input type="hidden" name="action" value="signup">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-at"></i> Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user-circle"></i> Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-key"></i> Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a strong password" required>
                </div>
                
                <button type="submit" class="btn-neon w-100">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="text-center mt-4" style="color: rgba(255,255,255,0.7);">
                <p>Experience the future of travel with NeonStay</p>
            </div>
        </div>
    </div>

    <script>
        function showLogin() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('signupForm').style.display = 'none';
            document.querySelectorAll('.btn-neon-outline').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.btn-neon-outline')[0].classList.add('active');
        }
        
        function showSignup() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('signupForm').style.display = 'block';
            document.querySelectorAll('.btn-neon-outline').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.btn-neon-outline')[1].classList.add('active');
        }
    </script>
</body>
</html>
