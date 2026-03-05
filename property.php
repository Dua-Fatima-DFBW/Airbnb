<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$property_id = $_GET['id'] ?? 0;

// Get property details
$stmt = $pdo->prepare("SELECT p.*, u.username as host_name, u.profile_image as host_image 
                      FROM properties p 
                      JOIN users u ON p.host_id = u.id 
                      WHERE p.id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: listings.php');
    exit;
}

// Get property reviews
$review_stmt = $pdo->prepare("SELECT r.*, u.username, u.profile_image 
                             FROM reviews r 
                             JOIN users u ON r.user_id = u.id 
                             WHERE r.property_id = ? 
                             ORDER BY r.created_at DESC 
                             LIMIT 5");
$review_stmt->execute([$property_id]);
$reviews = $review_stmt->fetchAll();

// Get similar properties
$similar_stmt = $pdo->prepare("SELECT * FROM properties 
                              WHERE property_type = ? AND id != ? AND is_available = 1 
                              ORDER BY rating DESC 
                              LIMIT 3");
$similar_stmt->execute([$property['property_type'], $property_id]);
$similar_properties = $similar_stmt->fetchAll();

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $guests = $_POST['guests'] ?? 1;
    
    if (empty($check_in) || empty($check_out)) {
        $error = "Please select check-in and check-out dates";
    } elseif ($guests > $property['max_guests']) {
        $error = "Number of guests exceeds maximum capacity";
    } else {
        // Calculate total price
        $date1 = new DateTime($check_in);
        $date2 = new DateTime($check_out);
        $nights = $date1->diff($date2)->days;
        $total_price = $nights * $property['price_per_night'];
        
        // Store in session for booking confirmation
        $_SESSION['booking_data'] = [
            'property_id' => $property_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests,
            'nights' => $nights,
            'total_price' => $total_price,
            'property_title' => $property['title'],
            'property_location' => $property['location']
        ];
        
        header('Location: booking.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> | NeonStay</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
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
                <a href="#" class="nav-link"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="#" class="nav-link"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <a href="logout.php" class="btn-neon-outline" style="margin-left: 15px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </header>
    
    <!-- Property Gallery -->
    <section style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <div class="neon-card" style="overflow: hidden; margin-bottom: 30px;">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; height: 400px;">
                <div style="overflow: hidden;">
                    <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" 
                         alt="Property main image" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="flex: 1; overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                             alt="Property image 2"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="flex: 1; overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                             alt="Property image 3"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 40px;">
            <!-- Property Details -->
            <div style="flex: 1;">
                <div class="neon-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                        <div>
                            <h1 style="font-size: 32px; color: white; margin-bottom: 10px;">
                                <?php echo htmlspecialchars($property['title']); ?>
                            </h1>
                            <div style="display: flex; align-items: center; gap: 15px; color: rgba(255,255,255,0.8);">
                                <span class="rating" style="font-size: 16px;">
                                    <i class="fas fa-star" style="color: #ffd700;"></i>
                                    <?php echo number_format($property['rating'], 1); ?> · 
                                    <?php echo $property['review_count']; ?> reviews
                                </span>
                                <span>
                                    <i class="fas fa-map-marker-alt" style="color: var(--neon-primary);"></i>
                                    <?php echo htmlspecialchars($property['location']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-certificate" style="color: var(--neon-primary);"></i>
                                    Superhost
                                </span>
                            </div>
                        </div>
                        
                        <!-- Save button -->
                        <button class="btn-neon-outline">
                            <i class="fas fa-heart"></i> Save
                        </button>
                    </div>
                    
                    <!-- Host Info -->
                    <div style="display: flex; align-items: center; gap: 15px; padding: 20px; background: rgba(0,255,157,0.05); border-radius: 15px; margin-bottom: 30px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid var(--neon-primary);">
                            <img src="https://randomuser.me/api/portraits/men/<?php echo rand(1, 99); ?>.jpg" 
                                 alt="Host" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <h3 style="color: white; margin-bottom: 5px;">
                                Hosted by <?php echo htmlspecialchars($property['host_name']); ?>
                            </h3>
                            <p style="color: rgba(255,255,255,0.7); font-size: 14px;">
                                Superhost · 5 years hosting
                            </p>
                        </div>
                    </div>
                    
                    <!-- Property Description -->
                    <div style="margin-bottom: 30px;">
                        <h2 style="color: var(--neon-primary); font-size: 24px; margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i> About this place
                        </h2>
                        <p style="color: rgba(255,255,255,0.9); line-height: 1.8; font-size: 16px;">
                            <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                        </p>
                    </div>
                    
                    <!-- Amenities -->
                    <div style="margin-bottom: 30px;">
                        <h2 style="color: var(--neon-primary); font-size: 24px; margin-bottom: 20px;">
                            <i class="fas fa-concierge-bell"></i> What this place offers
                        </h2>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                            <?php 
                            $amenities = json_decode($property['amenities'] ?? '[]', true);
                            foreach ($amenities as $amenity): 
                            ?>
                            <div style="display: flex; align-items: center; gap: 10px; padding: 10px; background: rgba(0,255,157,0.05); border-radius: 10px;">
                                <i class="fas fa-check" style="color: var(--neon-primary);"></i>
                                <span style="color: rgba(255,255,255,0.9);"><?php echo $amenity; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Reviews -->
                    <div>
                        <h2 style="color: var(--neon-primary); font-size: 24px; margin-bottom: 20px;">
                            <i class="fas fa-star"></i> Reviews
                            <span style="color: rgba(255,255,255,0.7); font-size: 18px;">
                                · <?php echo number_format($property['rating'], 1); ?> · 
                                <?php echo $property['review_count']; ?> reviews
                            </span>
                        </h2>
                        
                        <?php if (empty($reviews)): ?>
                            <div style="text-align: center; padding: 40px 20px; color: rgba(255,255,255,0.7);">
                                <i class="fas fa-comment-alt" style="font-size: 48px; margin-bottom: 15px;"></i>
                                <p>No reviews yet. Be the first to review this property!</p>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 25px;">
                                <?php foreach ($reviews as $review): ?>
                                <div style="padding: 20px; background: rgba(21, 21, 31, 0.5); border-radius: 15px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden;">
                                                <img src="https://randomuser.me/api/portraits/men/<?php echo rand(1, 99); ?>.jpg" 
                                                     alt="User" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div>
                                                <div style="color: white; font-weight: 600;"><?php echo htmlspecialchars($review['username']); ?></div>
                                                <div style="color: rgba(255,255,255,0.7); font-size: 12px;">
                                                    <?php echo date('F Y', strtotime($review['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star" style="color: <?php echo $i <= $review['rating'] ? '#ffd700' : 'rgba(255,255,255,0.3)'; ?>;"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p style="color: rgba(255,255,255,0.9); line-height: 1.6;">
                                        <?php echo htmlspecialchars($review['comment']); ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Booking Form -->
            <div style="width: 400px; flex-shrink: 0;">
                <div class="neon-card" style="position: sticky; top: 100px;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 25px;">
                        <div>
                            <div class="property-price" style="font-size: 28px;">
                                $<?php echo number_format($property['price_per_night'], 2); ?>
                                <small style="color: rgba(255,255,255,0.7); font-size: 16px;">/ night</small>
                            </div>
                            <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                                <i class="fas fa-star" style="color: #ffd700;"></i>
                                <?php echo number_format($property['rating'], 1); ?> · 
                                <?php echo $property['review_count']; ?> reviews
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error" style="margin-bottom: 20px;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div style="border: 2px solid var(--neon-border); border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr;">
                                <div style="padding: 15px; border-right: 1px solid var(--neon-border);">
                                    <label class="form-label">CHECK-IN</label>
                                    <input type="date" name="check_in" class="form-control" style="border: none; padding: 0; font-size: 16px;" required>
                                </div>
                                <div style="padding: 15px;">
                                    <label class="form-label">CHECKOUT</label>
                                    <input type="date" name="check_out" class="form-control" style="border: none; padding: 0; font-size: 16px;" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">GUESTS</label>
                            <select name="guests" class="form-control">
                                <?php for ($i = 1; $i <= $property['max_guests']; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> guest<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="book_now" class="btn-neon w-100" style="padding: 20px; font-size: 18px;">
                            <i class="fas fa-calendar-check"></i> Reserve Now
                        </button>
                        
                        <div style="text-align: center; margin-top: 15px; color: rgba(255,255,255,0.7); font-size: 14px;">
                            You won't be charged yet
                        </div>
                    </form>
                    
                    <!-- Price Breakdown -->
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(0,255,157,0.2);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: rgba(255,255,255,0.8);">
                            <span>$<?php echo number_format($property['price_per_night'], 2); ?> x 3 nights</span>
                            <span>$<?php echo number_format($property['price_per_night'] * 3, 2); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: rgba(255,255,255,0.8);">
                            <span>Cleaning fee</span>
                            <span>$50.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: rgba(255,255,255,0.8);">
                            <span>Service fee</span>
                            <span>$25.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(0,255,157,0.3); font-size: 18px; font-weight: bold; color: var(--neon-primary);">
                            <span>Total</span>
                            <span>$<?php echo number_format(($property['price_per_night'] * 3) + 75, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Similar Properties -->
                <?php if (!empty($similar_properties)): ?>
                <div style="margin-top: 30px;">
                    <h3 style="color: var(--neon-primary); margin-bottom: 20px; font-size: 20px;">
                        <i class="fas fa-home"></i> Similar Properties
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($similar_properties as $similar): ?>
                        <a href="property.php?id=<?php echo $similar['id']; ?>" style="text-decoration: none;">
                            <div class="neon-card" style="padding: 15px; display: flex; gap: 15px; align-items: center;">
                                <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80" 
                                     alt="Property" 
                                     style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover;">
                                <div style="flex: 1;">
                                    <div style="color: white; font-weight: 600; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($similar['title']); ?>
                                    </div>
                                    <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($similar['location']); ?>
                                    </div>
                                    <div style="color: var(--neon-primary); font-weight: bold; margin-top: 5px;">
                                        $<?php echo number_format($similar['price_per_night'], 2); ?> / night
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // Initialize date pickers with min date as tomorrow
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        const checkinInput = document.querySelector('input[name="check_in"]');
        const checkoutInput = document.querySelector('input[name="check_out"]');
        
        checkinInput.min = tomorrow.toISOString().split('T')[0];
        checkoutInput.min = tomorrow.toISOString().split('T')[0];
        
        // Set checkout min date based on checkin
        checkinInput.addEventListener('change', function() {
            const checkinDate = new Date(this.value);
            const minCheckout = new Date(checkinDate);
            minCheckout.setDate(minCheckout.getDate() + 1);
            checkoutInput.min = minCheckout.toISOString().split('T')[0];
            
            // If current checkout date is before new min, reset it
            if (checkoutInput.value && new Date(checkoutInput.value) < minCheckout) {
                checkoutInput.value = minCheckout.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
