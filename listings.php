<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Get search parameters
$location = $_GET['location'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$sort = $_GET['sort'] ?? 'rating_desc';
$type = $_GET['type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build query
$sql = "SELECT * FROM properties WHERE is_available = 1";
$params = [];
$types = [];

if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types[] = 's';
}

if (!empty($type)) {
    $sql .= " AND property_type = ?";
    $params[] = $type;
    $types[] = 's';
}

if (!empty($min_price) && is_numeric($min_price)) {
    $sql .= " AND price_per_night >= ?";
    $params[] = $min_price;
    $types[] = 'd';
}

if (!empty($max_price) && is_numeric($max_price)) {
    $sql .= " AND price_per_night <= ?";
    $params[] = $max_price;
    $types[] = 'd';
}

// Sorting
$sort_options = [
    'price_asc' => 'price_per_night ASC',
    'price_desc' => 'price_per_night DESC',
    'rating_desc' => 'rating DESC',
    'newest' => 'created_at DESC'
];
$sql .= " ORDER BY " . ($sort_options[$sort] ?? 'rating DESC');

// Execute query
if (!empty($params)) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    $stmt = $pdo->query($sql);
}
$properties = $stmt->fetchAll();

// Get unique property types for filter
$type_stmt = $pdo->query("SELECT DISTINCT property_type FROM properties ORDER BY property_type");
$property_types = $type_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeonStay | Explore Properties</title>
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
                <a href="listings.php" class="nav-link active"><i class="fas fa-search"></i> Explore</a>
                <a href="#" class="nav-link"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="#" class="nav-link"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <a href="logout.php" class="btn-neon-outline" style="margin-left: 15px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </header>
    
    <!-- Main Content -->
    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px; display: flex; gap: 30px;">
        <!-- Filters Sidebar -->
        <aside style="width: 300px; flex-shrink: 0;">
            <div class="neon-card" style="position: sticky; top: 100px;">
                <h3 style="color: var(--neon-primary); margin-bottom: 25px; font-size: 22px;">
                    <i class="fas fa-filter"></i> Filters
                </h3>
                
                <form method="GET" action="">
                    <!-- Location -->
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" 
                               placeholder="Search location..." value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                    
                    <!-- Dates -->
                    <div class="form-group">
                        <label class="form-label">Check-in Date</label>
                        <input type="date" name="checkin" class="form-control" value="<?php echo htmlspecialchars($checkin); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Check-out Date</label>
                        <input type="date" name="checkout" class="form-control" value="<?php echo htmlspecialchars($checkout); ?>">
                    </div>
                    
                    <!-- Property Type -->
                    <div class="form-group">
                        <label class="form-label">Property Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <?php foreach ($property_types as $ptype): ?>
                            <option value="<?php echo $ptype['property_type']; ?>" 
                                <?php echo $type == $ptype['property_type'] ? 'selected' : ''; ?>>
                                <?php echo $ptype['property_type']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="form-group">
                        <label class="form-label">Price Range</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="number" name="min_price" class="form-control" 
                                   placeholder="Min" value="<?php echo htmlspecialchars($min_price); ?>">
                            <input type="number" name="max_price" class="form-control" 
                                   placeholder="Max" value="<?php echo htmlspecialchars($max_price); ?>">
                        </div>
                    </div>
                    
                    <!-- Sort -->
                    <div class="form-group">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-control">
                            <option value="rating_desc" <?php echo $sort == 'rating_desc' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-neon w-100">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    
                    <a href="listings.php" class="btn-neon-outline w-100 mt-2" style="display: block; text-align: center;">
                        <i class="fas fa-redo"></i> Clear All
                    </a>
                </form>
            </div>
        </aside>
        
        <!-- Property Listings -->
        <main style="flex: 1;">
            <h1 style="font-size: 32px; color: white; margin-bottom: 30px;">
                <?php if (!empty($location)): ?>
                    Stays in <?php echo htmlspecialchars($location); ?>
                <?php else: ?>
                    All Properties
                <?php endif; ?>
                <span style="color: var(--neon-primary); font-size: 18px;">
                    (<?php echo count($properties); ?> results)
                </span>
            </h1>
            
            <?php if (empty($properties)): ?>
                <div class="neon-card text-center" style="padding: 60px 20px;">
                    <i class="fas fa-search" style="font-size: 64px; color: var(--neon-primary); margin-bottom: 20px;"></i>
                    <h2 style="color: white; margin-bottom: 15px;">No Properties Found</h2>
                    <p style="color: rgba(255,255,255,0.7);">Try adjusting your filters or search criteria</p>
                    <a href="listings.php" class="btn-neon mt-3">
                        <i class="fas fa-redo"></i> Reset Filters
                    </a>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px;">
                    <?php foreach ($properties as $property): ?>
                    <div class="neon-card">
                        <img src="https://images.unsplash.com/photo-<?php echo rand(151, 159); ?>969457719-0e0197ee?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>" 
                             class="property-image"
                             style="height: 250px; object-fit: cover; width: 100%;">
                        
                        <div class="card-content">
                            <!-- Badge -->
                            <div style="position: absolute; top: 20px; right: 20px; background: rgba(0,255,157,0.9); color: var(--neon-dark); padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px;">
                                <?php echo $property['property_type']; ?>
                            </div>
                            
                            <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                            
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            
                            <!-- Amenities -->
                            <div style="margin: 15px 0; display: flex; flex-wrap: wrap; gap: 10px;">
                                <?php 
                                $amenities = json_decode($property['amenities'] ?? '[]', true);
                                $display_amenities = array_slice($amenities, 0, 3);
                                foreach ($display_amenities as $amenity): 
                                ?>
                                <span style="background: rgba(0,255,157,0.1); color: var(--neon-primary); padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                    <i class="fas fa-check"></i> <?php echo $amenity; ?>
                                </span>
                                <?php endforeach; ?>
                                <?php if (count($amenities) > 3): ?>
                                <span style="color: rgba(255,255,255,0.7); font-size: 12px;">
                                    +<?php echo count($amenities) - 3; ?> more
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Property Details -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <div style="display: flex; gap: 20px;">
                                    <div style="text-align: center;">
                                        <div style="color: var(--neon-primary); font-weight: bold; font-size: 18px;">
                                            <?php echo $property['bedrooms']; ?>
                                        </div>
                                        <div style="color: rgba(255,255,255,0.7); font-size: 12px;">
                                            <i class="fas fa-bed"></i> Bedrooms
                                        </div>
                                    </div>
                                    
                                    <div style="text-align: center;">
                                        <div style="color: var(--neon-primary); font-weight: bold; font-size: 18px;">
                                            <?php echo $property['bathrooms']; ?>
                                        </div>
                                        <div style="color: rgba(255,255,255,0.7); font-size: 12px;">
                                            <i class="fas fa-bath"></i> Bathrooms
                                        </div>
                                    </div>
                                    
                                    <div style="text-align: center;">
                                        <div style="color: var(--neon-primary); font-weight: bold; font-size: 18px;">
                                            <?php echo $property['max_guests']; ?>
                                        </div>
                                        <div style="color: rgba(255,255,255,0.7); font-size: 12px;">
                                            <i class="fas fa-user-friends"></i> Guests
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="rating">
                                    <i class="fas fa-star" style="color: #ffd700;"></i>
                                    <?php echo number_format($property['rating'], 1); ?>
                                    <span style="color: rgba(255,255,255,0.7); font-size: 14px;">
                                        (<?php echo $property['review_count']; ?>)
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Price and Action -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid rgba(0,255,157,0.2);">
                                <div>
                                    <div class="property-price">$<?php echo number_format($property['price_per_night'], 2); ?> 
                                        <small style="color: rgba(255,255,255,0.7); font-size: 14px;">/ night</small>
                                    </div>
                                    <div style="color: rgba(255,255,255,0.7); font-size: 12px;">
                                        Total for 3 nights: $<?php echo number_format($property['price_per_night'] * 3, 2); ?>
                                    </div>
                                </div>
                                
                                <a href="property.php?id=<?php echo $property['id']; ?>" class="btn-neon">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <footer class="neon-footer">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; text-align: center;">
            <p style="color: rgba(255,255,255,0.7);">
                &copy; 2024 NeonStay. All rights reserved. | 
                <a href="#" style="color: var(--neon-primary); text-decoration: none;">Privacy Policy</a> | 
                <a href="#" style="color: var(--neon-primary); text-decoration: none;">Terms of Service</a>
            </p>
        </div>
    </footer>
</body>
</html>
