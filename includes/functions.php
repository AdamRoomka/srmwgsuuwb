<?php

function formatDatePl(string $date): string
{
    $timestamp = strtotime($date);
    return date('d.m.Y H:i', $timestamp);
}

function updateLastActivity(PDO $pdo, ?array $currentUser): void
{
    if (!$currentUser) {
        return;
    }

    $lastUpdate = $_SESSION['last_activity_update'] ?? 0;
    $now = time();

    if ($now - $lastUpdate > 300) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = :id");
            $stmt->execute(['id' => $currentUser['id']]);
            $_SESSION['last_activity_update'] = $now;
        } catch (PDOException $e) {

        }
    }
}

function getAvailableSeats(array $event, array $occupiedSeatsByEvent): int
{
    $occupiedCount = count($occupiedSeatsByEvent[(int) $event['id']] ?? []);
    $available = (int) $event['total_seats'] - $occupiedCount;
    return max(0, $available);
}

function statusLabel(string $status): string
{
    return match ($status) {
        'PLANOWANE' => 'PLANOWANE',
        'ZAMKNIĘTE' => 'ZAMKNIĘTE',
        'ANULOWANE' => 'ANULOWANE',
        default => $status,
    };
}