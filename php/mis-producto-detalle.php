<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';

$idProductoCliente = (int) ($_GET['id'] ?? 0);
$idCliente = (int) ($_SESSION['id_cliente'] ?? 0);
$producto = null;
$errorMessage = null;

if ($idProductoCliente > 0) {
    try {
        $stmt = $conexion->prepare(
            "SELECT pc.id_producto_cliente, pc.fecha_compra, pc.fecha_fin_garantia, pc.numero_factura, p.nombre AS producto, p.codigo_producto, p.descripcion
             FROM producto_cliente pc
             JOIN producto_catalogo p ON p.id_producto_catalogo = pc.id_producto_catalogo
             WHERE pc.id_producto_cliente = :id_producto_cliente AND pc.id_cliente = :id_cliente"
        );
        $stmt->execute([
            'id_producto_cliente' => $idProductoCliente,
            'id_cliente' => $idCliente,
        ]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = 'No se pudo cargar el producto. ' . $e->getMessage();
    }
} else {
    $errorMessage = 'No se indicó un producto válido.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del producto</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <button id="toggle-btn" class="toggle-btn" aria-label="Abrir menú">☰</button>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="sidebar" id="sidebar" aria-label="Menú lateral">
        <button id="close-btn" class="close-btn" aria-label="Cerrar menú">✕</button>
        <nav class="sidebar-menu">
            <a href="dashboard-cliente.php" class="sidebar-item">🏠 Dashboard</a>
            <a href="mis-productos.php" class="sidebar-item active">📦 Mis productos</a>
            <a href="mis-reclamos.php" class="sidebar-item">⚠️ Mis reclamos</a>
            <a href="../php/logout.php" class="sidebar-item">🔒 Cerrar sesión</a>
        </nav>
    </aside>

    <div class="dashboard-container">
        <nav class="layout-navbar navbar">
            <div class="navbar-brand">Detalle del producto</div>
            <div class="navbar-nav">
                <a href="../php/logout.php" class="btn btn-outline">Cerrar sesión</a>
            </div>
        </nav>

        <main class="layout-main">
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <?php if ($producto): ?>
                <section class="card">
                    <div class="card-header"><?= htmlspecialchars($producto['producto']) ?></div>
                    <div class="card-body">
                        <p><strong>Código:</strong> <?= htmlspecialchars($producto['codigo_producto'] ?? '-') ?></p>
                        <p><strong>Factura:</strong> <?= htmlspecialchars($producto['numero_factura'] ?? '-') ?></p>
                        <p><strong>Fecha de compra:</strong> <?= htmlspecialchars($producto['fecha_compra']) ?></p>
                        <p><strong>Fin de garantía:</strong> <?= htmlspecialchars($producto['fecha_fin_garantia'] ?? '-') ?></p>
                        <p><strong>Descripción:</strong> <?= htmlspecialchars($producto['descripcion'] ?? '-') ?></p>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="../js/hamburguer-menu.js"></script>
</body>
</html>
