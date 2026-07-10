<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/reclamo_estado.php';

$idReclamo = (int) ($_GET['id'] ?? 0);
$idCliente = (int) ($_SESSION['id_cliente'] ?? 0);
$reclamo = null;
$errorMessage = null;

if ($idReclamo > 0) {
    try {
        $stmt = $conexion->prepare(
            "SELECT r.id_reclamo, r.descripcion_problema, r.estado, r.fecha_recepcion, r.fecha_revision, r.fecha_resolucion, p.nombre AS producto, t.nombre AS tecnico
             FROM reclamo r
             JOIN producto_cliente pc ON pc.id_producto_cliente = r.id_producto_cliente
             JOIN producto_catalogo p ON p.id_producto_catalogo = pc.id_producto_catalogo
             LEFT JOIN tecnico t ON t.id_tecnico = r.id_tecnico
             WHERE r.id_reclamo = :id_reclamo AND pc.id_cliente = :id_cliente"
        );
        $stmt->execute([
            'id_reclamo' => $idReclamo,
            'id_cliente' => $idCliente,
        ]);
        $reclamo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = 'No se pudo cargar el reclamo. ' . $e->getMessage();
    }
} else {
    $errorMessage = 'No se indicó un reclamo válido.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del reclamo</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <button id="toggle-btn" class="toggle-btn" aria-label="Abrir menú">☰</button>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="sidebar" id="sidebar" aria-label="Menú lateral">
        <button id="close-btn" class="close-btn" aria-label="Cerrar menú">✕</button>
        <nav class="sidebar-menu">
            <a href="dashboard-cliente.php" class="sidebar-item">🏠 Dashboard</a>
            <a href="mis-productos.php" class="sidebar-item">📦 Mis productos</a>
            <a href="mis-reclamos.php" class="sidebar-item active">⚠️ Mis reclamos</a>
            <a href="../php/logout.php" class="sidebar-item">🔒 Cerrar sesión</a>
        </nav>
    </aside>

    <div class="dashboard-container">
        <nav class="layout-navbar navbar">
            <div class="navbar-brand">Detalle del reclamo</div>
            <div class="navbar-nav">
                <a href="../php/logout.php" class="btn btn-outline">Cerrar sesión</a>
            </div>
        </nav>

        <main class="layout-main">
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <?php if ($reclamo): ?>
                <section class="card">
                    <div class="card-header">Reclamo #<?= (int) $reclamo['id_reclamo'] ?></div>
                    <div class="card-body">
                        <p><strong>Producto:</strong> <?= htmlspecialchars($reclamo['producto'] ?? '-') ?></p>
                        <p><strong>Estado:</strong> <?= htmlspecialchars(estadoTexto($reclamo['estado'] ?? 'pendiente')) ?></p>
                        <p><strong>Fecha de recepción:</strong> <?= htmlspecialchars($reclamo['fecha_recepcion'] ?? '-') ?></p>
                        <p><strong>Fecha de revisión:</strong> <?= htmlspecialchars($reclamo['fecha_revision'] ?? '-') ?></p>
                        <p><strong>Fecha de resolución:</strong> <?= htmlspecialchars($reclamo['fecha_resolucion'] ?? '-') ?></p>
                        <p><strong>Técnico:</strong> <?= htmlspecialchars($reclamo['tecnico'] ?? 'Sin asignar') ?></p>
                        <p><strong>Descripción:</strong> <?= htmlspecialchars($reclamo['descripcion_problema'] ?? '-') ?></p>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="../js/hamburguer-menu.js"></script>
</body>
</html>
