<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';
require_once __DIR__ . '/reclamo_estado.php';

$reclamos = [];
$errorMessage = null;

try {
    $stmt = $conexion->query(
        "SELECT r.id_reclamo, r.estado, r.fecha_recepcion, r.descripcion_problema, c.nombre AS cliente, p.nombre AS producto, t.nombre AS tecnico
         FROM reclamo r
         LEFT JOIN producto_cliente pc ON pc.id_producto_cliente = r.id_producto_cliente
         LEFT JOIN cliente c ON c.id_cliente = pc.id_cliente
         LEFT JOIN producto_catalogo p ON p.id_producto_catalogo = pc.id_producto_catalogo
         LEFT JOIN tecnico t ON t.id_tecnico = r.id_tecnico
         ORDER BY CASE
            WHEN r.estado = 'pendiente' THEN 0
            WHEN r.estado = 'asignado' THEN 1
            WHEN r.estado IN ('en_revision', 'en revisión', 'en_proceso', 'en proceso') THEN 2
            WHEN r.estado = 'resuelto' THEN 3
            WHEN r.estado = 'rechazado' THEN 4
            ELSE 5
         END, r.fecha_recepcion ASC, r.id_reclamo ASC"
    );
    $reclamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'No se pudo cargar la lista de reclamos. ' . $e->getMessage();
}

render_view('reclamos.html', [
    'reclamos' => $reclamos,
    'errorMessage' => $errorMessage,
]);
