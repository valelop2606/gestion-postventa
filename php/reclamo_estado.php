<?php

function normalizar_estado_reclamo(string $estado): string {
    $estado = strtolower(trim($estado));

    return match ($estado) {
        'asignado' => 'asignado',
        'en revision', 'en_revision', 'en revisión', 'en_proceso', 'en proceso' => 'en_revision',
        'resuelto' => 'resuelto',
        'rechazado' => 'rechazado',
        default => 'pendiente',
    };
}

function estadoTexto(string $estado): string {
    return match (normalizar_estado_reclamo($estado)) {
        'asignado' => 'Asignado',
        'en_revision' => 'En revisión',
        'resuelto' => 'Resuelto',
        'rechazado' => 'Rechazado',
        default => 'Pendiente',
    };
}

function badgeClass(string $estado): string {
    return match (normalizar_estado_reclamo($estado)) {
        'asignado' => 'badge-primary',
        'en_revision' => 'badge-warning',
        'resuelto' => 'badge-success',
        'rechazado' => 'badge-danger',
        default => 'badge-pending',
    };
}

function ordenEstadoReclamo(string $estado): int {
    return match (normalizar_estado_reclamo($estado)) {
        'pendiente' => 0,
        'asignado' => 1,
        'en_revision' => 2,
        'resuelto' => 3,
        'rechazado' => 4,
        default => 5,
    };
}