<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['booking_confirmation'])) {
    header('Location: home.php');
    exit;
}

$confirmation = $_SESSION['booking_confirmation'];
$booking_id = $confirmation['booking_id'];

// Clear confirmation data
unset($_SESSION['booking_confirmation']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | NeonStay</title>
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
    
    <!-- Header -->
    <header class="neon-header">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div class="logo">
                <a href="home.php" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-home"></i> NeonStay
                </a>
            </div>
            
            <nav>
                <a href="home.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="listings.php" class="nav-link"><i class="fas fa-search"></i> Explore</a>
                <a href="#" class="nav-link"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <a href="logout.php" class="btn-neon-outline" style="margin-left: 15px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </header>
    
    <!-- Confirmation -->
    <section style="max-width: 600px; margin: 100px auto; padding: 0 20px;">
        <div class="neon-card" style="text-align: center; padding: 60px 40px;">
            <!-- Success Animation -->
            <div style="width: 100px; height: 100px; background: var(--neon-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; animation: neon-pulse 2s infinite;">
                <i class="fas fa-check" style="font-size: 48px; color: var(--neon-dark);"></i>
            </div>
            
            <h1 style="font-size: 36px; color: white; margin-bottom: 15px;">
                Booking Confirmed!
            </h1>
            
            <p style="color: rgba(255,255,255,0.8); font-size: 18px; margin-bottom: 40px;">
                Your stay has been successfully booked. We've sent a confirmation email to 
                <span style="color: var(--neon-primary);"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
            </p>
            
            <!-- Booking Details -->
            <div style="background: rgba(0,255,157,0.05); padding: 25px; border-radius: 15px; margin-bottom: 40px;">
                <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 25px;">
                    <div style="text-align: center;">
                        <div style="color: var(--neon-primary); font-size: 14px; margin-bottom: 5px;">Booking ID</div>
                        <div style="color: white; font-size: 20px; font-weight: 800;">#NS<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    
                    <div style="text-align: center;">
                        <div style="color: var(--neon-primary); font-size: 14px; margin-bottom: 5px;">Check-in</div>
                        <div style="color: white; font-size: 18px; font-weight: 600;">
                            <?php echo date('M j, Y', strtotime($confirmation['check_in'])); ?>
                        </div>
                    </div>
                    
                    <div style="text-align: center;">
                        <div style="color: var(--neon-primary); font-size: 14px; margin-bottom: 5px;">Total</div>
                        <div style="color: white; font-size: 20px; font-weight: 800;">
                            $<?php echo number_format($confirmation['total_price'] + 90, 2); ?>
                        </div>
                    </div>
                </div>
                
                <div style="color: rgba(255,255,255,0.8); line-height: 1.8;">
                    <i class="fas fa-info-circle" style="color: var(--neon-primary);"></i>
                    You'll receive check-in instructions from your host 24 hours before arrival.
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 20px; justify-content: center;">
                <a href="home.php" class="btn-neon">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                
                <a href="#" class="btn-neon-outline">
                    <i class="fas fa-download"></i> Download Receipt
                </a>
                
                <a href="#" class="btn-neon-outline">
                    <i class="fas fa-calendar-alt"></i> View Trips
                </a>
            </div>
        </div>
        
        <!-- Next Steps -->
        <div class="neon-card" style="margin-top: 30px; padding: 30px;">
            <h2 style="color: var(--neon-primary); font-size: 22px; margin-bottom: 20px;">
                <i class="fas fa-list-check"></i> What's Next?
            </h2>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: rgba(0,255,157,0.05); border-radius: 10px;">
                    <div style="width: 40px; height: 40px; background: var(--neon-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-envelope" style="color: var(--neon-dark);"></i>
                    </div>
                    <div>
                        <div style="color: white; font-weight: 600; margin-bottom: 5px;">Check your email</div>
                        <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                            We've sent your booking confirmation and receipt to your email.
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: rgba(0,255,157,0.05); border-radius: 10px;">
                    <div style="width: 40px; height: 40px; background: var(--neon-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-phone" style="color: var(--neon-dark);"></i>
                    </div>
                    <div>
                        <div style="color: white; font-weight: 600; margin-bottom: 5px;">Contact your host</div>
                        <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                            You can message your host through the NeonStay app or website.
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: rgba(0,255,157,0.05); border-radius: 10px;">
                    <div style="width: 40px; height: 40px; background: var(--neon-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-calendar" style="color: var(--neon-dark);"></i>
                    </div>
                    <div>
                        <div style="color: white; font-weight: 600; margin-bottom: 5px;">Manage your trip</div>
                        <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                            View or cancel your booking anytime from your trips page.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <footer class="neon-footer">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; text-align: center;">
            <p style="color: rgba(255,255,255,0.7);">
                &copy; 2024 NeonStay. All rights reserved. | 
                <a href="#" style="color: var(--neon-primary); text-decoration: none;">Privacy Policy</a> | 
                <a href="#" style="color: var(--neon-primary); text-decoration: none;">Terms of Service</a>
            </p>
        </div>
    </footer>
    
    <script>
        // Confetti effect
        function createConfetti() {
            const colors = ['#00ff9d', '#ff00ff', '#00eeff', '#9d00ff', '#ff5500'];
            const container = document.querySelector('.neon-card');
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'absolute';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = '50%';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = '-20px';
                confetti.style.opacity = '0.8';
                
                container.appendChild(confetti);
                
                // Animation
                confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                });
                
                // Remove after animation
                setTimeout(() => confetti.remove(), 5000);
            }
        }
        
        // Start confetti
        setTimeout(createConfetti, 500);
        setTimeout(createConfetti, 1000);
        setTimeout(createConfetti, 1500);
    </script>
</body>
</html>
