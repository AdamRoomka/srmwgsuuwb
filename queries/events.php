<?php

$eventStmt = $pdo->query("
    SELECT id, name, description, start_at, duration_minutes, total_seats, status
    FROM events
    ORDER BY start_at ASC
");
$events = $eventStmt->fetchAll();

$seatStmt = $pdo->query("
    SELECT os.event_id, os.seat_number, os.user_id, u.first_name, u.last_name
    FROM occupied_seats os
    JOIN users u ON u.id = os.user_id
    WHERE os.status = 'AKTYWNA'
    ORDER BY os.event_id, os.seat_number
");
$occupiedSeatRows = $seatStmt->fetchAll();

foreach ($occupiedSeatRows as $row) {
    $eventId = (int) $row['event_id'];

    if (!isset($occupiedSeatsByEvent[$eventId])) {
        $occupiedSeatsByEvent[$eventId] = [];
    }

    $occupiedSeatsByEvent[$eventId][] = [
        'seat_number' => (int) $row['seat_number'],
        'user_id' => (int) $row['user_id'],
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
    ];
}