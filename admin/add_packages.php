<?php
require '../config/db.php';

$success = $error = $feature_success = $feature_error = "";

// Handle Package Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_package'])) {
    $property_id = $_POST['property_id'];
    $occupancy_type = $_POST['occupancy_type'];
    $package_type = $_POST['package_type'];
    $b2c_rate = $_POST['b2c_rate'];
    $extra_person_rate = $_POST['extra_person_rate'] ?? 0.00;

    try {
        $stmt = $pdo->prepare("INSERT INTO packages 
            (property_id, occupancy_type, package_type, b2c_rate, extra_person_rate) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$property_id, $occupancy_type, $package_type, $b2c_rate, $extra_person_rate]);
        $success = "Package added successfully!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Feature Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_feature'])) {
    $name = trim($_POST['feature_name']);
    $description = trim($_POST['description']);
    $default_price = !empty($_POST['default_price']) ? floatval($_POST['default_price']) : 0.00;

    $check = $pdo->prepare("SELECT id FROM features WHERE name = ?");
    $check->execute([$name]);

    if ($check->rowCount() > 0) {
        $feature_error = "Feature already exists.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO features (name, description, default_price) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $default_price]);
        $feature_success = "Feature added successfully!";
    }
}

$properties = $pdo->query("SELECT id, name FROM properties ORDER BY name")->fetchAll();
$features = $pdo->query("SELECT id, name FROM features ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Package Plan</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h2>Add New Package Plan</h2>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

    <form method="post">
        <input type="hidden" name="add_package" value="1">

        <label>Property:</label>
        <select name="property_id" required>
            <option value="">Select Property</option>
            <?php foreach ($properties as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Occupancy Type:</label>
        <select name="occupancy_type" required>
            <option value="">Select</option>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
        </select><br><br>

        <label>Package Type:</label>
        <select name="package_type" required>
            <option value="">Select</option>
            <option value="CP">CP</option>
            <option value="MAP">MAP</option>
        </select><br><br>

        <label>B2C Rate (₹):</label>
        <input type="number" name="b2c_rate" step="0.01" required><br><br>
        <label>Extra Person Rate (₹):</label>
        <input type="number" name="extra_person_rate" step="0.01" required><br><br>


        <button type="submit">Add Package</button>
    </form>

    <hr><br>
    <h3>Add New Feature</h3>

    <?php if ($feature_success): ?><p style="color:green;"><?= $feature_success ?></p><?php endif; ?>
    <?php if ($feature_error): ?><p style="color:red;"><?= $feature_error ?></p><?php endif; ?>

    <form method="post">
        <input type="hidden" name="add_feature" value="1">

        <label>Feature Name:</label>
        <input type="text" name="feature_name" required><br><br>

        <label for="description">Description</label>
        <textarea name="description" rows="3" cols="50"></textarea><br><br>

        <label>Default Price (optional):</label>
        <input type="number" name="default_price" step="0.01" placeholder="0.00"><br><br>

        <button type="submit" name="submit">Add Feature</button>
    </form>
</body>

</html>