<?php

$eventStmt = $pdo->query("
    SELECT id, name, description, start_at, duration_minutes, total_seats, status
    FROM events
    ORDER BY start_at ASC
");
$events = $eventStmt->fetchAll();

$seatStmt = $pdo->query("
    SELECT event_id, seat_number, user_id
    FROM occupied_seats
    WHERE status = 'AKTYWNA'
    ORDER BY event_id, seat_number
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
    ];
}