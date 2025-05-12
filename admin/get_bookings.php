<?php
require '../config/db.php';

$property_id = $_GET['property_id'] ?? null;
$room_id = $_GET['room_id'] ?? null;

$sql = "
    SELECT b.check_in_date, b.check_out_date, b.room_id, r.name AS room_name,
           COALESCE(g.name, 'Guest') AS guest_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    LEFT JOIN guests g ON b.guest_id = g.id
    WHERE 1
";
$params = [];

if (!empty($property_id)) {
    $sql .= " AND r.property_id = ?";
    $params[] = $property_id;
}

if (!empty($room_id)) {
    $sql .= " AND b.room_id = ?";
    $params[] = $room_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($bookings as $b) {
    $events[] = [
        'title' => $b['room_name'] . ' - ' . $b['guest_name'],
        'start' => $b['check_in_date'],
        'end'   => date('Y-m-d', strtotime($b['check_out_date'])), // exclusive
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
