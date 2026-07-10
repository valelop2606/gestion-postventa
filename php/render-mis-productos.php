<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';

$idCliente = (int) ($_SESSION['id_cliente'] ?? 0);
$productos = [];
$catalogoProductos = [];
$mensaje = null;

if (isset($_GET['success']) && $_GET['success'] === '1') {
    $mensaje = 'Producto registrado correctamente.';
}

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

    $stmtCatalogo = $conexion->query("SELECT id_producto_catalogo, nombre FROM producto_catalogo ORDER BY nombre");
    $catalogoProductos = $stmtCatalogo->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = 'No se pudieron cargar los productos. ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProductoCatalogo = (int) ($_POST['producto_catalogo'] ?? 0);
    $fechaCompra = trim($_POST['fecha_compra'] ?? '');
    $fechaFinGarantia = trim($_POST['fecha_fin_garantia'] ?? '');
    $numeroFactura = trim($_POST['numero_factura'] ?? '');

    if ($idProductoCatalogo > 0 && $fechaCompra !== '') {
        try {
            $stmtInsert = $conexion->prepare(
                "INSERT INTO producto_cliente (fecha_compra, fecha_fin_garantia, numero_factura, id_cliente, id_producto_catalogo)
                 VALUES (:fecha_compra, :fecha_fin_garantia, :numero_factura, :id_cliente, :id_producto_catalogo)"
            );
            $stmtInsert->execute([
                'fecha_compra' => $fechaCompra,
                'fecha_fin_garantia' => $fechaFinGarantia !== '' ? $fechaFinGarantia : null,
                'numero_factura' => $numeroFactura !== '' ? $numeroFactura : null,
                'id_cliente' => $idCliente,
                'id_producto_catalogo' => $idProductoCatalogo,
            ]);
            $mensaje = 'Producto registrado correctamente.';
            header('Location: mis-productos.php?success=1');
            exit();
        } catch (PDOException $e) {
            $mensaje = 'No se pudo registrar el producto. ' . $e->getMessage();
        }
    } else {
        $mensaje = 'Selecciona un producto y una fecha de compra.';
    }
}

render_view('mis-productos.html', [
    'productos' => $productos,
    'catalogoProductos' => $catalogoProductos,
    'mensaje' => $mensaje,
]);
