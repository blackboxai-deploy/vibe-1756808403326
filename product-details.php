<?php
$pageTitle = "Product Details";
include 'includes/header.php';
require_once 'config/database.php';

// Get product ID
$medicineId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($medicineId <= 0) {
    redirectWithMessage("products.php", "Product not found.", "error");
}

// Get medicine details
$stmt = $pdo->prepare("
    SELECT m.*, c.category_name 
    FROM medicines m 
    LEFT JOIN categories c ON m.category_id = c.category_id 
    WHERE m.medicine_id = ?
");
$stmt->execute([$medicineId]);
$medicine = $stmt->fetch();

if (!$medicine) {
    redirectWithMessage("products.php", "Product not found.", "error");
}

// Get related products from same category
$stmt = $pdo->prepare("
    SELECT * FROM medicines 
    WHERE category_id = ? AND medicine_id != ? 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$medicine['category_id'], $medicineId]);
$relatedProducts = $stmt->fetchAll();
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 2rem; color: #666;">
        <a href="index.php" style="color: #2c5aa0; text-decoration: none;">Home</a> › 
        <a href="products.php" style="color: #2c5aa0; text-decoration: none;">Products</a> › 
        <a href="products.php?category=<?php echo $medicine['category_id']; ?>" style="color: #2c5aa0; text-decoration: none;"><?php echo htmlspecialchars($medicine['category_name']); ?></a> › 
        <?php echo htmlspecialchars($medicine['name']); ?>
    </div>

    <!-- Product Details -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">
        
        <!-- Product Image -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <img src="<?php echo htmlspecialchars($medicine['image']); ?>" 
                 alt="<?php echo htmlspecialchars($medicine['name']); ?>" 
                 style="width: 100%; height: 400px; object-fit: cover; border-radius: 10px;"
                 onerror="this.src='https://placehold.co/400x400?text=Medicine+Product+Healthcare+Pharmacy'">
        </div>
        
        <!-- Product Info -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h1 style="color: #2c5aa0; margin-bottom: 1rem;"><?php echo htmlspecialchars($medicine['name']); ?></h1>
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span style="background: #e9ecef; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.9rem; color: #666;">
                    <?php echo htmlspecialchars($medicine['category_name']); ?>
                </span>
                <?php if ($medicine['prescription_required']): ?>
                    <span class="prescription-required">Prescription Required</span>
                <?php endif; ?>
            </div>
            
            <div style="font-size: 2rem; font-weight: 700; color: #28a745; margin-bottom: 1.5rem;">
                <?php echo formatPrice($medicine['price']); ?>
            </div>
            
            <div style="border: 1px solid #eee; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; background: #f8f9fa;">
                <h3 style="color: #2c5aa0; margin-bottom: 1rem;">Product Information</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <strong>Manufacturer:</strong><br>
                        <?php echo htmlspecialchars($medicine['manufacturer']); ?>
                    </div>
                    <div>
                        <strong>Dosage:</strong><br>
                        <?php echo htmlspecialchars($medicine['dosage']); ?>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <strong>Stock Available:</strong><br>
                        <span style="color: <?php echo ($medicine['stock_quantity'] > 0) ? '#28a745' : '#dc3545'; ?>;">
                            <?php echo ($medicine['stock_quantity'] > 0) ? $medicine['stock_quantity'] . ' units' : 'Out of Stock'; ?>
                        </span>
                    </div>
                    <div>
                        <strong>Expiry Date:</strong><br>
                        <?php echo date('M Y', strtotime($medicine['expiry_date'])); ?>
                    </div>
                </div>
            </div>
            
            <!-- Add to Cart Form -->
            <?php if ($medicine['stock_quantity'] > 0): ?>
                <?php if (isLoggedIn()): ?>
                    <form action="add-to-cart.php" method="POST">
                        <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                        
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                            <label for="quantity" style="font-weight: 500;">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                   max="<?php echo $medicine['stock_quantity']; ?>" 
                                   style="width: 80px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                            <span style="font-size: 0.9rem; color: #666;">
                                (Max: <?php echo $medicine['stock_quantity']; ?>)
                            </span>
                        </div>
                        
                        <button type="submit" class="btn btn-success" 
                                style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                            Add to Cart - <?php echo formatPrice($medicine['price']); ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php?redirect=<?php echo urlencode("product-details.php?id=$medicineId"); ?>" 
                       class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem; display: block; text-align: center;">
                        Login to Purchase
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn" style="width: 100%; background: #ccc; cursor: not-allowed; font-size: 1.1rem; padding: 1rem;" disabled>
                    Out of Stock
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Description -->
    <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 3rem;">
        <h2 style="color: #2c5aa0; margin-bottom: 1.5rem;">Description</h2>
        <p style="line-height: 1.8; color: #333; font-size: 1.1rem;">
            <?php echo nl2br(htmlspecialchars($medicine['description'])); ?>
        </p>
        
        <?php if ($medicine['prescription_required']): ?>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 5px; margin-top: 2rem;">
                <h4 style="color: #856404; margin-bottom: 0.5rem;">⚠️ Prescription Required</h4>
                <p style="color: #856404; margin: 0;">
                    This medicine requires a valid prescription from a licensed healthcare provider. 
                    Please ensure you have a prescription ready when placing your order.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div style="margin-bottom: 3rem;">
            <h2 style="color: #2c5aa0; text-align: center; margin-bottom: 2rem;">Related Products</h2>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($relatedProduct['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" 
                                 onerror="this.src='https://placehold.co/280x200?text=Medicine+Product+Healthcare+Pharmacy'">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($relatedProduct['name']); ?></h3>
                            <div class="product-price"><?php echo formatPrice($relatedProduct['price']); ?></div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <a href="product-details.php?id=<?php echo $relatedProduct['medicine_id']; ?>" 
                                   class="btn btn-primary" style="flex: 1;">View Details</a>
                                
                                <?php if ($relatedProduct['stock_quantity'] > 0 && isLoggedIn()): ?>
                                    <form action="add-to-cart.php" method="POST" style="flex: 1;">
                                        <input type="hidden" name="medicine_id" value="<?php echo $relatedProduct['medicine_id']; ?>">
                                        <button type="submit" class="btn btn-success btn-full">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>