<?php
require './config/db.php';

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? null;
$checkin = $_POST['check_in_date'] ?? '';
$checkout = $_POST['check_out_date'] ?? '';
$property_id = $_POST['property_id'] ?? '';
$package_id = $_POST['package_id'] ?? null;
$extra_persons = intval($_POST['extra_persons'] ?? 0);

// Validate
if (!$name || !$phone || !$checkin || !$checkout || !$property_id || !$package_id) {
    die("Missing required fields.");
}

$checkin_date = new DateTime($checkin);
$checkout_date = new DateTime($checkout);
$nights = $checkin_date->diff($checkout_date)->days;

if ($nights < 1) die("Invalid date selection.");

// Get package rates
$stmt = $pdo->prepare("SELECT b2c_rate, extra_person_rate FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$pkg = $stmt->fetch();

if (!$pkg) die("Invalid package selected.");

$b2cRate = $pkg['b2c_rate'];
$extraRate = $pkg['extra_person_rate'];

$total_price = ($nights * $b2cRate) + ($extra_persons * $extraRate);

// Find available room
$stmt = $pdo->prepare("
    SELECT r.id FROM rooms r
    LEFT JOIN bookings b ON r.id = b.room_id 
        AND NOT (
            b.check_out_date <= :checkin OR 
            b.check_in_date >= :checkout
        )
    WHERE r.property_id = :property_id 
        AND b.id IS NULL
    LIMIT 1
");
$stmt->execute([
    ':checkin' => $checkin,
    ':checkout' => $checkout,
    ':property_id' => $property_id
]);
$room = $stmt->fetch();
if (!$room) die("No room available.");

$room_id = $room['id'];

// Insert guest
$guestStmt = $pdo->prepare("INSERT INTO guests (name, email, phone) VALUES (?, ?, ?)");
$guestStmt->execute([$name, $email, $phone]);
$guest_id = $pdo->lastInsertId();

// Insert booking
$bookingStmt = $pdo->prepare("
    INSERT INTO bookings 
    (guest_id, room_id, check_in_date, check_out_date, total_price, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$bookingStmt->execute([
    $guest_id,
    $room_id,
    $checkin,
    $checkout,
    $total_price
]);

echo "✅ Booking successful with Package. Total: ₹$total_price";
