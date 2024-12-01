<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Sorting and Filtering Logic
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name'; // Default sort by name
$filterMinPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$filterMaxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_FLOAT_MAX;

$query = "SELECT * FROM products WHERE price BETWEEN ? AND ? ORDER BY $sort";
$stmt = $pdo->prepare($query);
$stmt->execute([$filterMinPrice, $filterMaxPrice]);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="product-container">
        <h2>Welcome!</h2>
        <p>Here are the available products:</p>

        <!-- Sort and Filter Form -->
        <form method="GET" class="filter-form">
            <label for="sort">Sort by:</label>
            <select name="sort" id="sort">
                <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                <option value="price" <?php echo $sort === 'price' ? 'selected' : ''; ?>>Price</option>
            </select>

            <label for="min_price">Min Price:</label>
            <input type="number" name="min_price" id="min_price" value="<?php echo htmlspecialchars($filterMinPrice); ?>" step="0.01">

            <label for="max_price">Max Price:</label>
            <input type="number" name="max_price" id="max_price" value="<?php echo htmlspecialchars($filterMaxPrice); ?>" step="0.01">

            <button type="submit">Apply</button>
        </form>

        <!-- Product List -->
        <ul class="product-list">
            <?php foreach ($products as $product): ?>
                <li>
                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                    <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>
</body>
</html>