<?php
require 'database_connection.php'; // Ensure this file correctly establishes a PDO connection

// Check if the product_id is provided in the URL
if (isset($_GET['product_id'])) {
    $id = intval($_GET['product_id']); // Convert product_id to an integer for security

    try {
        // Prepare and execute the SQL query to fetch the image
        $stmt = $pdo->prepare("SELECT product_image FROM medical_products WHERE product_id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn(); // Fetch the binary image data

        if ($image) {
            // If image data is found, output it with the correct headers
            header("Content-Type: image/jpeg"); // Adjust if images are stored in another format (e.g., image/png)
            echo $image;
        } else {
            // If no image is found, output a fallback/default image
            header("Content-Type: image/png");
            readfile('img/default-image.png'); // Path to your default image file
        }
    } catch (PDOException $e) {
        // Handle database-related errors
        error_log("Database error: " . $e->getMessage());
        header("Content-Type: text/plain");
        echo "Error fetching the image.";
    }
} else {
    // If product_id is missing, output a fallback/default image
    header("Content-Type: image/png");
    readfile('img/default-image.png'); // Path to your default image file
}
?>
