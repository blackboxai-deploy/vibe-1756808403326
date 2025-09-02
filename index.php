<?php
$pageTitle = "Home";
include 'includes/header.php';
require_once 'config/database.php';

// Get featured medicines (top 6)
$stmt = $pdo->prepare("SELECT * FROM medicines ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featuredMedicines = $stmt->fetchAll();

// Get categories for display
$stmt = $pdo->prepare("SELECT * FROM categories LIMIT 8");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero-section">
        <h2>Welcome to MediCare Store</h2>
        <p>Your trusted online pharmacy providing quality medicines and healthcare products delivered to your doorstep. Safe, reliable, and convenient healthcare shopping.</p>
        <a href="products.php" class="cta-button">Shop Now</a>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <h2 style="text-align: center; margin-bottom: 2rem; color: #2c5aa0;">Browse by Category</h2>
        <div class="products-grid">
            <?php foreach ($categories as $category): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://placehold.co/280x200?text=<?php echo urlencode($category['category_name']); ?>+medical+category+healthcare+pharmacy" 
                             alt="<?php echo htmlspecialchars($category['category_name']); ?>" 
                             onerror="this.style.display='none'">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="products.php?category=<?php echo $category['category_id']; ?>" class="btn btn-primary btn-full">Browse Products</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-section">
        <h2 style="text-align: center; margin: 3rem 0 2rem; color: #2c5aa0;">Featured Medicines</h2>
        <div class="products-grid">
            <?php foreach ($featuredMedicines as $medicine): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($medicine['image']); ?>" 
                             alt="<?php echo htmlspecialchars($medicine['name']); ?>" 
                             onerror="this.src='https://placehold.co/280x200?text=Medicine+Product+Healthcare+Pharmacy'">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($medicine['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars(substr($medicine['description'], 0, 100)); ?>...</p>
                        
                        <div class="product-meta">
                            <span class="dosage">Dosage: <?php echo htmlspecialchars($medicine['dosage']); ?></span>
                            <?php if ($medicine['prescription_required']): ?>
                                <span class="prescription-required">Prescription Required</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-price"><?php echo formatPrice($medicine['price']); ?></div>
                        
                        <div class="product-meta">
                            <span class="manufacturer">By: <?php echo htmlspecialchars($medicine['manufacturer']); ?></span>
                            <span class="stock-status">
                                <?php if ($medicine['stock_quantity'] > 0): ?>
                                    In Stock (<?php echo $medicine['stock_quantity']; ?>)
                                <?php else: ?>
                                    Out of Stock
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if ($medicine['stock_quantity'] > 0): ?>
                            <?php if (isLoggedIn()): ?>
                                <form action="add-to-cart.php" method="POST" style="margin-top: 1rem;">
                                    <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                                    <button type="submit" class="btn btn-success btn-full">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary btn-full">Login to Purchase</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-full" style="background: #ccc; cursor: not-allowed;" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Info Section -->
    <section style="background: white; padding: 3rem; border-radius: 10px; margin: 3rem 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <div style="text-align: center; max-width: 800px; margin: 0 auto;">
            <h2 style="color: #2c5aa0; margin-bottom: 2rem;">Why Choose MediCare Store?</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <div>
                    <h4 style="color: #28a745; margin-bottom: 1rem;">Quality Assured</h4>
                    <p>All medicines are sourced from licensed manufacturers and verified for quality and authenticity.</p>
                </div>
                <div>
                    <h4 style="color: #28a745; margin-bottom: 1rem;">Fast Delivery</h4>
                    <p>Quick and reliable delivery to your doorstep with proper packaging and temperature control.</p>
                </div>
                <div>
                    <h4 style="color: #28a745; margin-bottom: 1rem;">Secure Payments</h4>
                    <p>Safe and secure payment processing with multiple payment options for your convenience.</p>
                </div>
                <div>
                    <h4 style="color: #28a745; margin-bottom: 1rem;">Expert Support</h4>
                    <p>Professional pharmacists available to answer your questions and provide healthcare guidance.</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>