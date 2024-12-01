<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_name'], $_FILES['product_image'])) {
        $productName = $_POST['product_name'];
        $productBrand = $_POST['product_brand'];
        $productCategory = $_POST['product_category'];
        $productPrice = $_POST['product_price'];

        // Handle image upload
        $image = $_FILES['product_image'];
        $imagePath = '';

        // Check if image upload is successful
        if ($image['error'] === UPLOAD_ERR_OK) {
            $imageName = uniqid() . '_' . $image['name'];
            $imagePath = 'uploads/' . $imageName;

            // Ensure uploads directory exists
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            // Move the uploaded file to the desired location
            if (move_uploaded_file($image['tmp_name'], $imagePath)) {
                // Insert product data into the database
                $stmt = $pdo->prepare("INSERT INTO medical_products (product_name, product_brand, product_category, product_image, product_price) 
                                       VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$productName, $productBrand, $productCategory, $imagePath, $productPrice]);

                $successMessage = "Product added successfully!";
            } else {
                $errorMessage = "Failed to upload image. Please try again.";
            }
        } else {
            $errorMessage = "No image uploaded or an error occurred during upload.";
        }
    }

    // For product removal
    if (isset($_POST['remove_product'])) {
        $productId = $_POST['product_id'];

        if (!empty($productId)) {
            $stmt = $pdo->prepare("DELETE FROM medical_products WHERE product_id = ?");
            $stmt->execute([$productId]);
            $removeMessage = "Product removed successfully!";
        } else {
            $removeMessage = "Please provide a valid product ID.";
        }
    }

    // For product search
    if (isset($_POST['search_product'])) {
        $productId = $_POST['product_id'];
        $productName = $_POST['product_name'];
        $productCategory = $_POST['product_category'];

        $stmt = $pdo->prepare("SELECT * FROM medical_products WHERE product_id = ? OR product_name = ? OR product_category = ?");
        $stmt->execute([$productId, $productName, $productCategory]);

        $productDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css?v=26">
</head>
<body>

    <div class="product-management">
        <!-- Upload Product Section -->
        <div class="form-container">
            <h2>Add New Product</h2>
            <?php if (isset($successMessage)) echo "<p class='success-message'>$successMessage</p>"; ?>
            <?php if (isset($errorMessage)) echo "<p class='error-message'>$errorMessage</p>"; ?>
            <form method="POST" enctype="multipart/form-data">
                <label for="product_name">Product Name</label>
                <input type="text" name="product_name" required>

                <label for="product_brand">Product Brand</label>
                <input type="text" name="product_brand" required>

                <label for="product_category">Product Category</label>
                <input type="text" name="product_category" required>

                <label for="product_price">Product Price</label>
                <input type="number" name="product_price" required>

                <label for="product_image">Product Image</label>
                <div class="upload-area">
                    <input type="file" name="product_image" accept="image/*" required>
                </div>

                <button type="submit">Add Product</button>
            </form>
        </div>

        <!-- Search and Remove Product Section -->
        <div class="form-container">
            <h2>Search & Remove Product</h2>
            <?php if (isset($removeMessage)) echo "<p class='success-message'>$removeMessage</p>"; ?>
            <form method="POST">
                <label for="product_id">Product ID</label>
                <input type="text" name="product_id" placeholder="Search by product ID">

                <label for="product_name">Product Name</label>
                <input type="text" name="product_name" placeholder="Search by product name">

                <label for="product_category">Product Category</label>
                <input type="text" name="product_category" placeholder="Search by product category">

                <button type="submit" name="search_product">Search Product</button>
                <button type="submit" name="remove_product">Remove Product</button>
            </form>

            <!-- Display product details if found -->
            <?php if (isset($productDetails)): ?>
                <div class="product-details">
                    <h3>Product Details</h3>
                    <p><strong>Product ID:</strong> <?php echo $productDetails['product_id']; ?></p>
                    <p><strong>Product Name:</strong> <?php echo $productDetails['product_name']; ?></p>
                    <p><strong>Product Category:</strong> <?php echo $productDetails['product_category']; ?></p>
                    <p><strong>Price:</strong> <?php echo $productDetails['product_price']; ?></p>
                    <img src="<?php echo $productDetails['product_image']; ?>" alt="Product Image">
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
