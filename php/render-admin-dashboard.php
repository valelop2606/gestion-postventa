<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';
require_once __DIR__ . '/reclamo_estado.php';

$stats = [
    'pendientes' => 0,
    'resueltos' => 0,
    'tecnicos' => 0,
    'productos' => 0,
    'clientes' => 0,
    'stock_bajo' => 0,
];
$reclamosRecientes = [];
$errorMessage = null;

try {
    $queryStats = <<<SQL
        SELECT
            (SELECT COUNT(*) FROM reclamo WHERE estado = 'pendiente') AS pendientes,
            (SELECT COUNT(*) FROM reclamo WHERE estado = 'resuelto') AS resueltos,
            (SELECT COUNT(*) FROM tecnico) AS tecnicos,
            (SELECT COUNT(*) FROM producto_catalogo) AS productos,
            (SELECT COUNT(*) FROM cliente) AS clientes,
            (SELECT COUNT(*) FROM repuesto WHERE stock <= 5) AS stock_bajo
SQL;

    $stmtStats = $conexion->query($queryStats);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: $stats;

    $queryReclamos = <<<SQL
        SELECT r.id_reclamo, r.descripcion_problema, r.estado, r.fecha_recepcion, c.nombre AS cliente, p.nombre AS producto, t.nombre AS tecnico
        FROM reclamo r
        LEFT JOIN producto_cliente pc ON r.id_producto_cliente = pc.id_producto_cliente
        LEFT JOIN cliente c ON pc.id_cliente = c.id_cliente
        LEFT JOIN producto_catalogo p ON pc.id_producto_catalogo = p.id_producto_catalogo
        LEFT JOIN tecnico t ON r.id_tecnico = t.id_tecnico
        ORDER BY CASE
            WHEN r.estado = 'pendiente' THEN 0
            WHEN r.estado = 'asignado' THEN 1
            WHEN r.estado IN ('en_revision', 'en revisión', 'en_proceso', 'en proceso') THEN 2
            WHEN r.estado = 'resuelto' THEN 3
            WHEN r.estado = 'rechazado' THEN 4
            ELSE 5
        END, r.fecha_recepcion DESC, r.id_reclamo DESC
        LIMIT 8
SQL;

    $stmtReclamos = $conexion->query($queryReclamos);
    $reclamosRecientes = $stmtReclamos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'No se pudo cargar la información del panel. ' . $e->getMessage();
}

render_view('admin-dashboard.html', [
    'stats' => $stats,
    'reclamosRecientes' => $reclamosRecientes,
    'errorMessage' => $errorMessage,
]);
