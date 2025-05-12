<?php
require 'db.php';

if (!isset($_GET['property_id']) || !is_numeric($_GET['property_id'])) {
    die("Invalid property ID");
}

$property_id = $_GET['property_id'];

// Get property details
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("Property not found");
}

// Fetch one available room for price estimation
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE property_id = ? AND status = 'available' LIMIT 1");
$stmt->execute([$property_id]);
$exampleRoom = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exampleRoom) {
    die("No available rooms in this property.");
}

// Get total rooms
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE property_id = ?");
$stmt->execute([$property_id]);
$totalRooms = $stmt->fetchColumn();

// Fetch booking ranges for all rooms in this property
$stmt = $pdo->prepare("SELECT check_in_date, check_out_date FROM bookings 
    JOIN rooms ON bookings.room_id = rooms.id 
    WHERE rooms.property_id = ?");
$stmt->execute([$property_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count bookings per date
$dateCounts = [];
foreach ($bookings as $booking) {
    $start = new DateTime($booking['check_in_date']);
    $end = new DateTime($booking['check_out_date']);
    $interval = new DateInterval('P1D');
    $range = new DatePeriod($start, $interval, $end); // exclude checkout

    foreach ($range as $date) {
        $d = $date->format('Y-m-d');
        $dateCounts[$d] = ($dateCounts[$d] ?? 0) + 1;
    }
}

// Mark fully booked dates
$fullyBookedDates = [];
foreach ($dateCounts as $date => $count) {
    if ($count >= $totalRooms) {
        $fullyBookedDates[] = $date;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Room - <?= htmlspecialchars($property['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #28a745;
            color: white;
        }
        .flatpickr-day.disabled {
            background: #ddd !important;
            color: #999 !important;
            cursor: not-allowed;
        }
        .legend span {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 10px;
            border-radius: 5px;
            color: white;
        }
        .legend .available { background-color: #007bff; }
        .legend .selected  { background-color: #28a745; }
        .legend .booked    { background-color: #6c757d; }
    </style>
</head>
<body class="container py-4">
    <h2>Book a Room at <?= htmlspecialchars($property['name']) ?></h2>

    <form method="POST" action="process_booking.php">
        <input type="hidden" name="property_id" value="<?= $property_id ?>">
        <input type="hidden" name="base_price" value="<?= $exampleRoom['base_price'] ?>">

        <div class="mb-3">
            <label>Your Name *</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Phone Number *</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email (optional)</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label>Check-in Date *</label>
            <input type="text" name="check_in_date" id="checkIn" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Check-out Date *</label>
            <input type="text" name="check_out_date" id="checkOut" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Total Price:</label>
            <input type="text" id="totalPrice" class="form-control" readonly>
        </div>

        <button type="submit" class="btn btn-success">Confirm Booking</button>
    </form>


    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const fullyBooked = <?= json_encode($fullyBookedDates) ?>;
        const pricePerNight = <?= $exampleRoom['base_price'] ?>;
        const today = new Date().toISOString().split('T')[0];

const checkIn = flatpickr("#checkIn", {
    dateFormat: "Y-m-d",
    disable: fullyBooked,
    minDate: today, // prevent past dates
    onChange: function(selectedDates, dateStr) {
        checkOut.set("minDate", dateStr);
        calculatePrice();
    }
});

const checkOut = flatpickr("#checkOut", {
    dateFormat: "Y-m-d",
    disable: fullyBooked,
    minDate: today, // prevent past dates
    onChange: calculatePrice
});

        function calculatePrice() {
            const checkInDate = new Date(document.getElementById("checkIn").value);
            const checkOutDate = new Date(document.getElementById("checkOut").value);

            if (checkInDate && checkOutDate && checkOutDate > checkInDate) {
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                document.getElementById("totalPrice").value = `₹${nights * pricePerNight}`;
            } else {
                document.getElementById("totalPrice").value = '';
            }
        }
    </script>
</body>
</html>
