<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Get featured properties
$stmt = $pdo->query("SELECT * FROM properties WHERE is_available = 1 ORDER BY rating DESC LIMIT 6");
$featured_properties = $stmt->fetchAll();

// Handle search
$search_results = [];
$search_location = '';
$search_checkin = '';
$search_checkout = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_location = trim($_POST['location'] ?? '');
    $search_checkin = $_POST['checkin'] ?? '';
    $search_checkout = $_POST['checkout'] ?? '';
    
    $sql = "SELECT * FROM properties WHERE is_available = 1";
    $params = [];
    
    if (!empty($search_location)) {
        $sql .= " AND location LIKE ?";
        $params[] = "%$search_location%";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $search_results = $stmt->fetchAll();
    
    if (!empty($search_results)) {
        header("Location: listings.php?location=" . urlencode($search_location) . 
               "&checkin=" . urlencode($search_checkin) . 
               "&checkout=" . urlencode($search_checkout));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeonStay | Find Your Perfect Stay</title>
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
                <i class="fas fa-home"></i> NeonStay
            </div>
            
            <nav>
                <a href="home.php" class="nav-link active"><i class="fas fa-home"></i> Home</a>
                <a href="listings.php" class="nav-link"><i class="fas fa-search"></i> Explore</a>
                <a href="#" class="nav-link"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="#" class="nav-link"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <a href="logout.php" class="btn-neon-outline" style="margin-left: 15px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h1 class="hero-title">Find Your Neon Escape</h1>
            <p class="hero-subtitle">Discover unique stays with futuristic luxury and vibrant experiences</p>
            
            <!-- Search Bar -->
            <form method="POST" action="" class="search-container" style="display: flex; align-items: center; margin-top: 40px;">
                <div class="search-input-group" style="flex: 1;">
                    <i class="fas fa-map-marker-alt search-icon"></i>
                    <input type="text" name="location" class="search-input" placeholder="Where are you going?" value="<?php echo htmlspecialchars($search_location); ?>">
                </div>
                
                <div class="search-input-group" style="flex: 1;">
                    <i class="fas fa-calendar-alt search-icon"></i>
                    <input type="date" name="checkin" class="search-input" placeholder="Check-in" value="<?php echo htmlspecialchars($search_checkin); ?>">
                </div>
                
                <div class="search-input-group" style="flex: 1;">
                    <i class="fas fa-calendar-alt search-icon"></i>
                    <input type="date" name="checkout" class="search-input" placeholder="Check-out" value="<?php echo htmlspecialchars($search_checkout); ?>">
                </div>
                
                <button type="submit" name="search" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </section>
    
    <!-- Featured Properties -->
    <section style="max-width: 1200px; margin: 100px auto; padding: 0 20px;">
        <h2 style="font-size: 36px; font-weight: 800; color: white; margin-bottom: 40px; text-align: center;">
            <i class="fas fa-star" style="color: var(--neon-primary);"></i> Featured Stays
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php foreach ($featured_properties as $property): ?>
            <div class="neon-card">
                <img src="https://images.unsplash.com/photo-<?php echo rand(151, 159); ?>969457719-0e0197ee?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
                
                <div class="card-content">
                    <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                    
                    <p class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($property['location']); ?>
                    </p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <?php echo number_format($property['rating'], 1); ?> (<?php echo $property['review_count']; ?>)
                        </div>
                        
                        <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                            <i class="fas fa-user-friends"></i> <?php echo $property['max_guests']; ?> guests
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="property-price">$<?php echo number_format($property['price_per_night'], 2); ?> <small>/ night</small></div>
                        </div>
                        
                        <a href="property.php?id=<?php echo $property['id']; ?>" class="btn-neon-outline">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Categories -->
    <section style="max-width: 1200px; margin: 100px auto; padding: 0 20px;">
        <h2 style="font-size: 36px; font-weight: 800; color: white; margin-bottom: 40px; text-align: center;">
            <i class="fas fa-th-large" style="color: var(--neon-primary);"></i> Browse by Category
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">
            <div class="neon-card text-center" style="padding: 40px 20px;">
                <i class="fas fa-building" style="font-size: 48px; color: var(--neon-primary); margin-bottom: 20px;"></i>
                <h3 style="color: white; margin-bottom: 10px;">Apartments</h3>
                <p style="color: rgba(255,255,255,0.7);">Modern city apartments</p>
            </div>
            
            <div class="neon-card text-center" style="padding: 40px 20px;">
                <i class="fas fa-home" style="font-size: 48px; color: var(--neon-secondary); margin-bottom: 20px;"></i>
                <h3 style="color: white; margin-bottom: 10px;">Houses</h3>
                <p style="color: rgba(255,255,255,0.7);">Entire homes for families</p>
            </div>
            
            <div class="neon-card text-center" style="padding: 40px 20px;">
                <i class="fas fa-umbrella-beach" style="font-size: 48px; color: var(--neon-blue); margin-bottom: 20px;"></i>
                <h3 style="color: white
