<?php
require '../config/db.php'; // your PDO connection
// Load properties for filter dropdown
$properties = $pdo->query("SELECT id, name FROM properties")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Room-wise Booking Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <style>
        #filters {
            margin: 20px;
        }
        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

<div id="filters">
    <label>Property:</label>
    <select id="propertyFilter">
        <option value="">All Properties</option>
        <?php foreach ($properties as $prop): ?>
            <option value="<?= $prop['id'] ?>"><?= htmlspecialchars($prop['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Room:</label>
    <select id="roomFilter">
        <option value="">All Rooms</option>
    </select>
</div>

<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        events: function(fetchInfo, successCallback, failureCallback) {
            const propertyId = document.getElementById('propertyFilter').value;
            const roomId = document.getElementById('roomFilter').value;

            fetch(`get_bookings.php?property_id=${propertyId}&room_id=${roomId}`)
                .then(res => res.json())
                .then(data => successCallback(data))
                .catch(err => failureCallback(err));
        },
        eventDidMount: function(info) {
            tippy(info.el, {
                content: info.event.title,
            });
        }
    });
    calendar.render();

    document.getElementById('propertyFilter').addEventListener('change', function () {
        loadRooms(this.value);
        calendar.refetchEvents();
    });

    document.getElementById('roomFilter').addEventListener('change', function () {
        calendar.refetchEvents();
    });

    function loadRooms(propertyId) {
        fetch(`get_rooms.php?property_id=${propertyId}`)
            .then(res => res.json())
            .then(data => {
                const roomSelect = document.getElementById('roomFilter');
                roomSelect.innerHTML = '<option value="">All Rooms</option>';
                data.forEach(room => {
                    roomSelect.innerHTML += `<option value="${room.id}">${room.name}</option>`;
                });
            });
    }
});
</script>

</body>
</html>
