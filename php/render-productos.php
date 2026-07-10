<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';

$productos = [];
$errorMessage = null;

try {
    $stmt = $conexion->query(
        "SELECT id_producto_catalogo, nombre, codigo_producto, descripcion FROM producto_catalogo ORDER BY nombre"
    );
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'No se pudo cargar la lista de productos. ' . $e->getMessage();
}

render_view('productos.html', [
    'productos' => $productos,
    'errorMessage' => $errorMessage,
]);
