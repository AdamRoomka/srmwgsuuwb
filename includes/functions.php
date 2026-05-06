<?php

function formatDatePl(string $date): string
{
    $timestamp = strtotime($date);
    return date('d.m.Y H:i', $timestamp);
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