<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';

$idProducto = (int) ($_GET['id'] ?? 0);
$producto = null;
$errorMessage = null;

if ($idProducto > 0) {
    try {
        $stmt = $conexion->prepare(
            "SELECT id_producto_catalogo, nombre, codigo_producto, descripcion FROM producto_catalogo WHERE id_producto_catalogo = :id"
        );
        $stmt->execute(['id' => $idProducto]);
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
    <title>Detalle de producto</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <button id="toggle-btn" class="toggle-btn" aria-label="Abrir menú">☰</button>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="sidebar" id="sidebar" aria-label="Menú lateral">
        <button id="close-btn" class="close-btn" aria-label="Cerrar menú">✕</button>
        <nav class="sidebar-menu">
            <a href="admin-dashboard.php" class="sidebar-item">🏠 Dashboard</a>
            <a href="tecnicos.php" class="sidebar-item">👨‍🔧 Técnicos</a>
            <a href="productos.php" class="sidebar-item active">📦 Productos</a>
            <a href="reclamos.php" class="sidebar-item">⚠️ Reclamos</a>
            <a href="../php/logout.php" class="sidebar-item">🔒 Cerrar sesión</a>
        </nav>
    </aside>

    <div class="dashboard-container">
        <nav class="layout-navbar navbar">
            <div class="navbar-brand">Detalle de producto</div>
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
                    <div class="card-header"><?= htmlspecialchars($producto['nombre']) ?></div>
                    <div class="card-body">
                        <p><strong>Código:</strong> <?= htmlspecialchars($producto['codigo_producto']) ?></p>
                        <p><strong>Descripción:</strong> <?= htmlspecialchars($producto['descripcion'] ?? '-') ?></p>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="../js/hamburguer-menu.js"></script>
</body>
</html>
