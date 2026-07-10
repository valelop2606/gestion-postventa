<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';
require_once __DIR__ . '/reclamo_estado.php';

$idCliente = (int) ($_SESSION['id_cliente'] ?? 0);
$productos = [];
$reclamos = [];
$errorMessage = null;

try {
    $stmtProductos = $conexion->prepare(
        "SELECT pc.id_producto_cliente, pc.fecha_compra, pc.fecha_fin_garantia, pc.numero_factura, p.nombre AS producto
         FROM producto_cliente pc
         JOIN producto_catalogo p ON p.id_producto_catalogo = pc.id_producto_catalogo
         WHERE pc.id_cliente = :id_cliente
         ORDER BY pc.fecha_compra DESC"
    );
    $stmtProductos->execute(['id_cliente' => $idCliente]);
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    $stmtReclamos = $conexion->prepare(
        "SELECT r.id_reclamo, r.descripcion_problema, r.estado, r.fecha_recepcion, p.nombre AS producto
         FROM reclamo r
         JOIN producto_cliente pc ON pc.id_producto_cliente = r.id_producto_cliente
         JOIN producto_catalogo p ON p.id_producto_catalogo = pc.id_producto_catalogo
         WHERE pc.id_cliente = :id_cliente
         ORDER BY CASE
            WHEN r.estado = 'pendiente' THEN 0
            WHEN r.estado = 'asignado' THEN 1
            WHEN r.estado IN ('en_revision', 'en revisión', 'en_proceso', 'en proceso') THEN 2
            WHEN r.estado = 'resuelto' THEN 3
            WHEN r.estado = 'rechazado' THEN 4
            ELSE 5
         END, r.fecha_recepcion ASC"
    );
    $stmtReclamos->execute(['id_cliente' => $idCliente]);
    $reclamos = $stmtReclamos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'No se pudo cargar el resumen del cliente. ' . $e->getMessage();
}

render_view('dashboard-cliente.html', [
    'productos' => $productos,
    'reclamos' => $reclamos,
    'errorMessage' => $errorMessage,
]);
