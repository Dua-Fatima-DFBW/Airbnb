<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check if booking data exists in session
if (!isset($_SESSION['booking_data'])) {
    header('Location: home.php');
    exit;
}

$booking_data = $_SESSION['booking_data'];
$user_id = $_SESSION['user_id'];

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    try {
        // Insert booking into database
        $stmt = $pdo->prepare("INSERT INTO bookings (property_id, user_id, check_in, check_out, guests, total_price, status, payment_status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'confirmed', 'pending')");
        
        $stmt->execute([
            $booking_data['property_id'],
            $user_id,
            $booking_data['check_in'],
            $booking_data['check_out'],
            $booking_data['guests'],
            $booking_data['total_price']
        ]);
        
        $booking_id = $pdo->lastInsertId();
        
        // Clear booking data from session
        unset($_SESSION['booking_data']);
        
        // Redirect to confirmation page
        $_SESSION['booking_confirmation'] = [
            'booking_id' => $booking_id,
            'property_title' => $booking_data['property_title'],
            'check_in' => $booking_data['check_in'],
            'check_out' => $booking_data['check_out'],
            'guests' => $booking_data['guests'],
            'total_price' => $booking_data['total_price']
        ];
        
        header('Location: book_process.php');
        exit;
        
    } catch (PDOException $e) {
        $error = "Booking failed: " . $e->getMessage();
    }
}

// Get property details for display
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$booking_data['property_id']]);
$property = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Booking | NeonStay</title>
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
    
    <!-- Booking Process -->
    <section style="max-width: 800px; margin: 50px auto; padding: 0 20px;">
        <!-- Progress Steps -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px; position: relative;">
            <div style="position: absolute; top: 15px; left: 0; right: 0; height: 3px; background: rgba(0,255,157,0.2); z-index: 1;"></div>
            
            <div style="display: flex; flex-direction: column; align-items: center; z-index: 2;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--neon-primary); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <i class="fas fa-check" style="color: var(--neon-dark);"></i>
                </div>
                <span style="color: var(--neon-primary); font-weight: 600;">Details</span>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center; z-index: 2;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--neon-primary); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="color: var(--neon-dark); font-weight: bold;">2</span>
                </div>
                <span style="color: var(--neon-primary); font-weight: 600;">Review</span>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center; z-index: 2;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: rgba(0,255,157,0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="color: rgba(255,255,255,0.7); font-weight: bold;">3</span>
                </div>
                <span style="color: rgba(255,255,255,0.7);">Confirm</span>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="neon-card">
            <h1 style="font-size: 28px; color: white; margin-bottom: 30px; text-align: center;">
                <i class="fas fa-calendar-check"></i> Review Your Booking
            </h1>
            
            <div style="display: flex; gap: 30px; margin-bottom: 40px;">
                <!-- Property Info -->
                <div style="flex: 1;">
                    <h2 style="color: var(--neon-primary); font-size: 22px; margin-bottom: 20px;">
                        Your Stay
                    </h2>
                    
                    <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 25px;">
                        <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80" 
                             alt="Property" 
                             style="width: 120px; height: 100px; border-radius: 10px; object-fit: cover;">
                        <div>
                            <h3 style="color: white; font-size: 18px; margin-bottom: 5px;">
                                <?php echo htmlspecialchars($booking_data['property_title']); ?>
                            </h3>
                            <p style="color: rgba(255,255,255,0.7); font-size: 14px;">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($booking_data['property_location']); ?>
                            </p>
                            <div class="rating" style="font-size: 14px;">
                                <i class="fas fa-star" style="color: #ffd700;"></i>
                                <?php echo number_format($property['rating'], 1); ?> · 
                                <?php echo $property['review_count']; ?> reviews
                            </div>
                        </div>
                    </div>
                    
                    <!-- Trip Details -->
                    <div style="background: rgba(0,255,157,0.05); padding: 20px; border-radius: 15px;">
                        <h3 style="color: var(--neon-primary); margin-bottom: 15px; font-size: 18px;">
                            <i class="fas fa-calendar-alt"></i> Trip Details
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 14px; margin-bottom: 5px;">Check-in</div>
                                <div style="color: white; font-weight: 600;">
                                    <?php echo date('D, M j, Y', strtotime($booking_data['check_in'])); ?>
                                </div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 14px;">After 3:00 PM</div>
                            </div>
                            
                            <div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 14px; margin-bottom: 5px;">Check-out</div>
                                <div style="color: white; font-weight: 600;">
                                    <?php echo date('D, M j, Y', strtotime($booking_data['check_out'])); ?>
                                </div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 14px;">Before 11:00 AM</div>
                            </div>
                            
                            <div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 14px; margin-bottom: 5px;">Guests</div>
                                <div style="color: white; font-weight: 600;">
                                    <?php echo $booking_data['guests']; ?> guest<?php echo $booking_data['guests'] > 1 ? 's' : ''; ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 14px; margin-bottom: 5px;">Duration</div>
                                <div style="color: white; font-weight: 600;">
                                    <?php echo $booking_data['nights']; ?> night<?php echo $booking_data['nights'] > 1 ? 's' : ''; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Price Summary -->
                <div style="width: 300px; flex-shrink: 0;">
                    <div class="neon-card" style="background: rgba(0,255,157,0.05);">
                        <h2 style="color: var(--neon-primary); font-size: 22px; margin-bottom: 20px;">
                            Price Details
                        </h2>
                        
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: rgba(255,255,255,0.9);">
                                <span>$<?php echo number_format($property['price_per_night'], 2); ?> x <?php echo $booking_data['nights']; ?> nights</span>
                                <span>$<?php echo number_format($property['price_per_night'] * $booking_data['nights'], 2); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: rgba(255,255,255,0.9);">
                                <span>Cleaning fee</span>
                                <span>$50.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: rgba(255,255,255,0.9);">
                                <span>Service fee</span>
                                <span>$25.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: rgba(255,255,255,0.9);">
                                <span>Occupancy taxes and fees</span>
                                <span>$15.00</span>
                            </div>
                        </div>
                        
                        <div style="border-top: 2px solid var(--neon-border); padding-top: 15px; margin-top: 15px;">
                            <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; color: var(--neon-primary);">
                                <span>Total</span>
                                <span>$<?php echo number_format(($property['price_per_night'] * $booking_data['nights']) + 90, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div style="margin-bottom: 40px;">
                <h2 style="color: var(--neon-primary); font-size: 22px; margin-bottom: 20px;">
                    <i class="fas fa-credit-card"></i> Payment Method
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div style="padding: 15px; border: 2px solid var(--neon-primary); border-radius: 10px; text-align: center; cursor: pointer;">
                        <i class="fab fa-cc-visa" style="font-size: 32px; color: white; margin-bottom: 10px;"></i>
                        <div style="color: white; font-weight: 600;">Visa / Mastercard</div>
                    </div>
                    
                    <div style="padding: 15px; border: 2px solid rgba(255,255,255,0.2); border-radius: 10px; text-align: center; cursor: pointer;">
                        <i class="fab fa-cc-paypal" style="font-size: 32px; color: white; margin-bottom: 10px;"></i>
                        <div style="color: white; font-weight: 600;">PayPal</div>
                    </div>
                    
                    <div style="padding: 15px; border: 2px solid rgba(255,255,255,0.2); border-radius: 10px; text-align: center; cursor: pointer;">
                        <i class="fab fa-google-pay" style="font-size: 32px; color: white; margin-bottom: 10px;"></i>
                        <div style="color: white; font-weight: 600;">Google Pay</div>
                    </div>
                </div>
                
                <!-- Card Details -->
                <div style="background: rgba(21, 21, 31, 0.5); padding: 25px; border-radius: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div>
                            <label class="form-label">Expiration Date</label>
                            <input type="month" class="form-control">
                        </div>
                        <div>
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" placeholder="123" maxlength="3" style="width: 100px;">
                        </div>
                        <div>
                            <label class="form-label">Country</label>
                            <select class="form-control">
                                <option>United States</option>
                                <option>United Kingdom</option>
                                <option>Canada</option>
                                <option>Australia</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="color: rgba(255,255,255,0.7); font-size: 12px; line-height: 1.6;">
                        <i class="fas fa-lock"></i> Your payment information is encrypted and secure.
                        By proceeding, you agree to our <a href="#" style="color: var(--neon-primary);">Terms of Service</a> 
                        and <a href="#" style="color: var(--neon-primary);">Privacy Policy</a>.
                    </div>
                </div>
            </div>
            
            <!-- Confirmation -->
            <div style="text-align: center;">
                <form method="POST" action="">
                    <button type="submit" name="confirm_booking" class="btn-neon" style="padding: 20px 60px; font-size: 18px;">
                        <i class="fas fa-check-circle"></i> Confirm & Book Now
                    </button>
                </form>
                
                <div style="color: rgba(255,255,255,0.7); margin-top: 20px; font-size: 14px;">
                    <i class="fas fa-shield-alt"></i> Free cancellation for 48 hours. 
                    <a href="#" style="color: var(--neon-primary);">Learn more</a>
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
        // Format card number input
        document.querySelector('input[placeholder="1234 5678 9012 3456"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 16) value = value.substr(0, 16);
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value;
        });
        
        // Format CVV input
        document.querySelector('input[placeholder="123"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3) value = value.substr(0, 3);
            e.target.value = value;
        });
    </script>
</body>
</html>
