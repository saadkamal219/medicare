<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'database_connection.php';

session_start(); // Start the session to manage tokens

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'];
    $productBrand = $_POST['product_brand'];
    $productCategory = $_POST['product_category'];
    $productPrice = $_POST['product_price'];

    // Handle image upload
    $image = $_FILES['product_image'];
    $imagePath = '';

    if ($image['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '_' . $image['name'];
        $imagePath = 'uploads/' . $imageName;

        // Ensure uploads directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        move_uploaded_file($image['tmp_name'], $imagePath);
    }

    // Insert product data into database
    $stmt = $pdo->prepare("INSERT INTO medical_products (product_name, product_brand, product_category, product_image, product_price) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$productName, $productBrand, $productCategory, $imagePath, $productPrice]);

    $successMessage = "Product added successfully!";
}

// Handle adding blood donor data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_donor'])) {
    // Check for duplicate form submissions using session token
    if (isset($_SESSION['last_token']) && $_SESSION['last_token'] === $_POST['form_token']) {
        die("Duplicate submission detected!");
    }

    // Store the current token to avoid duplicates
    $_SESSION['last_token'] = $_POST['form_token']; 

    try {
        $donorName = $_POST['donor_name'];
        $donorBloodGroup = $_POST['donor_blood_group'];
        $donorPhoneNumber = $_POST['donor_phone_number'];
        $donorDivision = $_POST['donor_division'];
        $donorDistrict = $_POST['donor_district'];
        $donorCity = $_POST['donor_city'];

        if (empty($donorName) || empty($donorBloodGroup) || empty($donorPhoneNumber)) {
            die("Error: All fields are required!");
        }

        // Insert donor data into the database
        $stmt = $pdo->prepare("INSERT INTO blood_donor (donor_name, donor_blood_group, donor_phone_number, donor_divison, donor_district, donor_city) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$donorName, $donorBloodGroup, $donorPhoneNumber, $donorDivision, $donorDistrict, $donorCity]);

        // After successful insertion, set a session message and redirect to avoid double submission
        $_SESSION['donor_message'] = "Donor added successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
    }
}

// Handle donor removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_donor'])) {
    $searchKey = $_POST['search_key'];
    $searchValue = $_POST['search_value'];

    $stmt = $pdo->prepare("SELECT * FROM blood_donor WHERE $searchKey LIKE ?");
    $stmt->execute(['%' . $searchValue . '%']);
    $donors = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_donor'])) {
    $donorId = $_POST['donor_id'];

    $stmt = $pdo->prepare("DELETE FROM blood_donor WHERE id = ?");
    $stmt->execute([$donorId]);

    $_SESSION['donor_message'] = "Donor removed successfully!";
}

// Handle donor removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_donor'])) {
    $searchName = $_POST['search_name'] ?? '';
    $searchBloodGroup = $_POST['search_blood_group'] ?? '';
    $searchPhoneNumber = $_POST['search_phone_number'] ?? '';
    $searchCity = $_POST['search_city'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM blood_donor WHERE donor_name LIKE ? AND donor_blood_group LIKE ? AND donor_phone_number LIKE ? AND donor_city LIKE ?");
    $stmt->execute(['%' . $searchName . '%', '%' . $searchBloodGroup . '%', '%' . $searchPhoneNumber . '%', '%' . $searchCity . '%']);
    $donors = $stmt->fetchAll();
}

// Handle donor removal after search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_donor'])) {
    $donorId = $_POST['donor_id'];

    $stmt = $pdo->prepare("DELETE FROM blood_donor WHERE id = ?");
    $stmt->execute([$donorId]);

    $_SESSION['donor_message'] = "Donor removed successfully!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image" href="img/short_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=24">
    <title>Admin Portal - Add Product</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 25px;
            background: linear-gradient(135deg, #ffffff, #f0f4f8);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .form-container:hover {
            transform: translateY(-5px);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }

        input[type="text"], input[type="number"], input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            background: #f9f9f9;
        }

        input[type="text"]:focus, input[type="number"]:focus, input[type="file"]:focus {
            border-color: #007bff;
            outline: none;
            background: #fff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success-message {
            text-align: center;
            color: green;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
        }

        .upload-area {
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px dashed #ddd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .upload-area:hover {
            border-color: #007bff;
        }

        .upload-area label {
            cursor: pointer;
            color: #555;
            font-size: 14px;
            font-weight: bold;
        }

        footer {
            text-align: center;
            padding: 10px 0;
            margin-top: 30px;
            background: #007bff;
            color: white;
            font-size: 14px;
        }

        footer a {
            color: #fff;
            text-decoration: underline;
        }

        /* Notification style */
        .notification {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <?php if (isset($_SESSION['donor_message'])): ?>
        <div class="notification" id="notification">
            <?php echo $_SESSION['donor_message']; ?>
        </div>
        <?php unset($_SESSION['donor_message']); ?>
    <?php endif; ?>

    <div class="form-container">
        <h2>Add New Product</h2>
        <form method="POST" enctype="multipart/form-data" action="">
            <label for="product_name">Product Name</label>
            <input type="text" name="product_name" id="product_name" placeholder="Enter product name" required>

            <label for="product_brand">Product Brand</label>
            <input type="text" name="product_brand" id="product_brand" placeholder="Enter product brand" required>

            <label for="product_category">Product Category</label>
            <input type="text" name="product_category" id="product_category" placeholder="Enter product category" required>

            <label for="product_price">Product Price</label>
            <input type="number" name="product_price" id="product_price" placeholder="Enter product price" required>

            <label for="product_image">Product Image</label>
            <div class="upload-area">
                <label for="product_image">Click to upload an image</label>
                <input type="file" name="product_image" id="product_image" accept="image/*" style="display: none;" required>
            </div>

            <button type="submit">Add Product</button>
        </form>
    </div>

    <div class="form-container">
        <h2>Add Blood Donor</h2>
        <form method="POST">
            <input type="hidden" name="form_token" value="<?php echo uniqid(); ?>">
            <label for="donor_name">Donor Name</label>
            <input type="text" name="donor_name" placeholder="Enter donor name" required>

            <label for="donor_blood_group">Blood Group</label>
            <select name="donor_blood_group" required>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>

            <label for="donor_phone_number">Phone Number</label>
            <input type="text" name="donor_phone_number" placeholder="Enter donor phone number" required>

            <label for="donor_division">Division</label>
            <input type="text" name="donor_division" placeholder="Enter donor division" required>

            <label for="donor_district">District</label>
            <input type="text" name="donor_district" placeholder="Enter donor district" required>

            <label for="donor_city">City</label>
            <input type="text" name="donor_city" placeholder="Enter donor city" required>

            <button type="submit" name="add_donor" id="submit-button">Add Donor</button>
        </form>
    </div>

    <!-- Search and Delete Donor Form -->
    <div class="form-container">
        <h2>Search and Delete Donor</h2>
        <form method="POST">
            <label for="search_name">Donor Name</label>
            <input type="text" name="search_name" placeholder="Search by name" value="<?php echo $_POST['search_name'] ?? ''; ?>">

            <label for="search_blood_group">Blood Group</label>
            <select name="search_blood_group">
                <option value="">Select Blood Group</option>
                <option value="A+" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'A+') echo 'selected'; ?>>A+</option>
                <option value="A-" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'A-') echo 'selected'; ?>>A-</option>
                <option value="B+" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'B+') echo 'selected'; ?>>B+</option>
                <option value="B-" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'B-') echo 'selected'; ?>>B-</option>
                <option value="AB+" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'AB+') echo 'selected'; ?>>AB+</option>
                <option value="AB-" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'AB-') echo 'selected'; ?>>AB-</option>
                <option value="O+" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'O+') echo 'selected'; ?>>O+</option>
                <option value="O-" <?php if (isset($_POST['search_blood_group']) && $_POST['search_blood_group'] == 'O-') echo 'selected'; ?>>O-</option>
            </select>

            <label for="search_phone_number">Phone Number</label>
            <input type="text" name="search_phone_number" placeholder="Search by phone number" value="<?php echo $_POST['search_phone_number'] ?? ''; ?>">

            <label for="search_city">City</label>
            <input type="text" name="search_city" placeholder="Search by city" value="<?php echo $_POST['search_city'] ?? ''; ?>">

            <button type="submit" name="search_donor">Search Donor</button>
        </form>
    </div>

    <?php if (isset($donors) && count($donors) > 0): ?>
        <div class="form-container">
            <h3>Search Results</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Blood Group</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donors as $donor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donor['donor_name']); ?></td>
                            <td><?php echo htmlspecialchars($donor['donor_blood_group']); ?></td>
                            <td><?php echo htmlspecialchars($donor['donor_phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($donor['donor_city']); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to remove this donor?');">
                                    <input type="hidden" name="donor_id" value="<?php echo $donor['id']; ?>">
                                    <button type="submit" name="remove_donor">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>


    <footer>
        <p>&copy; 2024 Your Company | <a href="#">Privacy Policy</a></p>
    </footer>

    <script>
        // Show notification when donor is added successfully
        window.onload = function() {
            var notification = document.getElementById("notification");
            if (notification) {
                notification.style.display = "block";
                setTimeout(function() {
                    notification.style.display = "none";
                }, 5000); // Hide notification after 5 seconds
            }
        };

        // window.onload = function() {
        //     var notification = document.getElementById("notification");
        //     if (notification) {
        //         notification.style.display = "block";
        //         setTimeout(function() {
        //             notification.style.display = "none";
        //         }, 5000); // Hide notification after 5 seconds
        //     }
        // };
    </script>

</body>
</html>
