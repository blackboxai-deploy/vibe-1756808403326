<?php
$pageTitle = "Products";
include 'includes/header.php';
require_once 'config/database.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'name';

// Build query
$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(name LIKE ? OR description LIKE ? OR manufacturer LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($category > 0) {
    $whereConditions[] = "category_id = ?";
    $params[] = $category;
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Sort options
$sortOptions = [
    'name' => 'name ASC',
    'price_low' => 'price ASC',
    'price_high' => 'price DESC',
    'newest' => 'created_at DESC'
];

$orderClause = "ORDER BY " . ($sortOptions[$sort] ?? $sortOptions['name']);

// Get medicines
$sql = "SELECT m.*, c.category_name FROM medicines m 
        LEFT JOIN categories c ON m.category_id = c.category_id 
        $whereClause $orderClause";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$medicines = $stmt->fetchAll();

// Get all categories for filter
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: #2c5aa0;">
            <?php if (!empty($search)): ?>
                Search Results for "<?php echo htmlspecialchars($search); ?>"
            <?php elseif ($category > 0): ?>
                <?php 
                $currentCategory = array_filter($categories, function($cat) use ($category) {
                    return $cat['category_id'] == $category;
                });
                if (!empty($currentCategory)) {
                    echo htmlspecialchars(array_values($currentCategory)[0]['category_name']);
                }
                ?>
            <?php else: ?>
                All Products
            <?php endif; ?>
        </h1>
        <div style="color: #666;">
            Found <?php echo count($medicines); ?> products
        </div>
    </div>

    <!-- Filters and Sort -->
    <div style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="GET" action="products.php" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            
            <div class="form-group" style="margin: 0; min-width: 200px;">
                <label for="search" style="margin-bottom: 0.25rem; font-size: 0.9rem;">Search:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search medicines..." style="padding: 0.5rem;">
            </div>
            
            <div class="form-group" style="margin: 0; min-width: 150px;">
                <label for="category" style="margin-bottom: 0.25rem; font-size: 0.9rem;">Category:</label>
                <select id="category" name="category" style="padding: 0.5rem;">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" 
                                <?php echo ($category == $cat['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin: 0; min-width: 150px;">
                <label for="sort" style="margin-bottom: 0.25rem; font-size: 0.9rem;">Sort by:</label>
                <select id="sort" name="sort" style="padding: 0.5rem;">
                    <option value="name" <?php echo ($sort == 'name') ? 'selected' : ''; ?>>Name A-Z</option>
                    <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="align-self: end;">Apply Filters</button>
            
            <?php if (!empty($search) || $category > 0): ?>
                <a href="products.php" class="btn" style="background: #6c757d; color: white; align-self: end;">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products Grid -->
    <?php if (empty($medicines)): ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="color: #666; margin-bottom: 1rem;">No products found</h3>
            <p style="color: #999;">Try adjusting your search criteria or browse all categories.</p>
            <a href="products.php" class="btn btn-primary" style="margin-top: 1rem;">View All Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($medicines as $medicine): ?>
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
                            <span class="category"><?php echo htmlspecialchars($medicine['category_name']); ?></span>
                            <span class="dosage">Dosage: <?php echo htmlspecialchars($medicine['dosage']); ?></span>
                        </div>
                        
                        <?php if ($medicine['prescription_required']): ?>
                            <div class="product-meta">
                                <span class="prescription-required">Prescription Required</span>
                            </div>
                        <?php endif; ?>
                        
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
                        
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <a href="product-details.php?id=<?php echo $medicine['medicine_id']; ?>" 
                               class="btn btn-primary" style="flex: 1;">View Details</a>
                            
                            <?php if ($medicine['stock_quantity'] > 0): ?>
                                <?php if (isLoggedIn()): ?>
                                    <form action="add-to-cart.php" method="POST" style="flex: 1;">
                                        <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                                        <button type="submit" class="btn btn-success btn-full">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-success" style="flex: 1;">Login to Buy</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn" style="background: #ccc; cursor: not-allowed; flex: 1;" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>