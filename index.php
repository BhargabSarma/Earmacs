<?php
session_start();

// If admin is already logged in, redirect to the admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/view_properties.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Welcome to Property Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Welcome to the Property Management System</h1>
    <p><a href="admin/register.php">Register Admin</a></p>
    <p><a href="admin/login.php">Login as Admin</a></p>
</body>

</html>