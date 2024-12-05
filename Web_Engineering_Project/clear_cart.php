<?php

require 'database_connection.php';

try {
    $stmt = $pdo->prepare("DELETE FROM cart");
    $stmt->execute();
    echo "Cart cleared successfully!";
} catch (PDOException $e) {
    echo "Error clearing cart: " . $e->getMessage();
}
?>
