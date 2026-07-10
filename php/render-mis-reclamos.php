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
$mensaje = null;

if (isset($_GET['success']) && $_GET['success'] === '1') {
    $mensaje = 'Reclamo registrado correctamente.';
}

try {
    $stmtProductos = $conexion->prepare(
        "SELECT pc.id_producto_cliente, p.nombre AS producto
         FROM producto_cliente pc
         JOIN producto_catalogo p ON p.id_producto_catalogo = pc.id_producto_catalogo
         WHERE pc.id_cliente = :id_cliente
         ORDER BY p.nombre"
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
    $mensaje = 'No se pudieron cargar los reclamos. ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProductoCliente = (int) ($_POST['producto_cliente'] ?? 0);
    $descripcion = trim($_POST['descripcion_problema'] ?? '');
    $estado = 'pendiente';

    if ($idProductoCliente > 0 && $descripcion !== '') {
        try {
            $stmtInsert = $conexion->prepare(
                "INSERT INTO reclamo (descripcion_problema, fecha_recepcion, estado, id_producto_cliente, id_tecnico)
                 VALUES (:descripcion_problema, CURRENT_DATE, :estado, :id_producto_cliente, NULL)"
            );
            $stmtInsert->execute([
                'descripcion_problema' => $descripcion,
                'estado' => $estado,
                'id_producto_cliente' => $idProductoCliente,
            ]);
            $mensaje = 'Reclamo registrado correctamente.';
            header('Location: mis-reclamos.php?success=1');
            exit();
        } catch (PDOException $e) {
            $mensaje = 'No se pudo registrar el reclamo. ' . $e->getMessage();
        }
    } else {
        $mensaje = 'Selecciona un producto y escribe una descripción del problema.';
    }
}

render_view('mis-reclamos.html', [
    'productos' => $productos,
    'reclamos' => $reclamos,
    'mensaje' => $mensaje,
]);
