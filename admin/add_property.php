<?php include '../config/db.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Property</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h2>Add New Property</h2>
    <form method="post" action="">
        <label>Property Name:</label>
        <input type="text" name="name" required><br>

        <label>Location:</label>
        <input type="text" name="location" required><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" cols="50"></textarea><br>

        <button type="submit" name="submit">Add Property</button>
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $location = $_POST['location'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare("INSERT INTO properties (name, location, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $location, $description]);

        echo "<p style='color: green;'>Property added successfully!</p>";
    }
    ?>
</body>

</html>