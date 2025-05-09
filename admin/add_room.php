<?php include '../config/db.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Room</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .feature-box {
            margin-bottom: 8px;
        }

        .feature-box input[type="number"] {
            width: 80px;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <h2>Add Room to Property</h2>

    <form method="post" action="">
        <!-- Property Dropdown -->
        <label for="property_id">Select Property:</label>
        <select name="property_id" required>
            <option value="">-- Select Property --</option>
            <?php
            $stmt = $pdo->query("SELECT id, name FROM properties ORDER BY name");
            while ($row = $stmt->fetch()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select><br><br>

        <!-- Room Name -->
        <label>Room Name:</label>
        <input type="text" name="name" required><br><br>

        <!-- Room Type Dropdown -->
        <label for="room_type">Room Type:</label>
        <select name="room_type" required>
            <option value="">-- Select Room Type --</option>
            <option value="single_bed">Single Bed</option>
            <option value="double_bed">Double Bed</option>
            <option value="suite">Suite</option>
            <option value="2_bedroom">2 Bedroom</option>
            <option value="3_bedroom">3 Bedroom</option>
            <option value="family_suite">Family Suite</option>
        </select><br><br>

        <!-- Base Price -->
        <label>Base Price:</label>
        <input type="number" name="base_price" step="0.01" required><br><br>

        <!-- Description -->
        <label>Description:</label><br>
        <textarea name="description" rows="4" cols="50"></textarea><br><br>

        <!-- Status -->
        <label>Status:</label>
        <select name="status">
            <option value="available">Available</option>
            <option value="booked">Booked</option>
            <option value="maintenance">Maintenance</option>
        </select><br><br>

        <!-- Features Selection -->
        <label>Features (optional):</label><br>
        <?php
        $feature_stmt = $pdo->query("SELECT * FROM features ORDER BY name");
        while ($feature = $feature_stmt->fetch()) {
            echo "
        <div class='feature-box'>
            <input type='checkbox' name='features[]' value='{$feature['id']}' id='feature_{$feature['id']}'>
            <label for='feature_{$feature['id']}'>{$feature['name']}</label>
            &nbsp;&nbsp;
            <span>Price: ₹" . number_format($feature['default_price'], 2) . "</span>
            <input type='hidden' name='feature_prices[{$feature['id']}]' value='{$feature['default_price']}'>
        </div>
    ";
        }
        ?>

        <br>

        <button type="submit" name="submit">Add Room</button>
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $property_id = $_POST['property_id'];
        $name = $_POST['name'];
        $room_type = $_POST['room_type'];
        $base_price = $_POST['base_price'];
        $description = $_POST['description'];
        $status = $_POST['status'];

        // Insert room
        $stmt = $pdo->prepare("INSERT INTO rooms (property_id, name, room_type, base_price, description, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$property_id, $name, $room_type, $base_price, $description, $status]);

        $room_id = $pdo->lastInsertId();

        // Insert room features
        if (!empty($_POST['features'])) {
            foreach ($_POST['features'] as $feature_id) {
                $price = isset($_POST['feature_prices'][$feature_id]) ? floatval($_POST['feature_prices'][$feature_id]) : 0.00;

                $f_stmt = $pdo->prepare("INSERT INTO room_features (room_id, feature_id, price) VALUES (?, ?, ?)");
                $f_stmt->execute([$room_id, $feature_id, $price]);
            }
        }

        echo "<p style='color: green;'>Room and features added successfully!</p>";
    }
    ?>
</body>

</html>