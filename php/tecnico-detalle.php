<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';

$idTecnico = (int) ($_GET['id'] ?? 0);
$tecnico = null;
$errorMessage = null;

if ($idTecnico > 0) {
    try {
        $stmt = $conexion->prepare(
            "SELECT id_tecnico, nombre, correo, telefono, ciudad, pais, direccion, fecha_contratacion FROM tecnico WHERE id_tecnico = :id"
        );
        $stmt->execute(['id' => $idTecnico]);
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = 'No se pudo cargar el técnico. ' . $e->getMessage();
    }
} else {
    $errorMessage = 'No se indicó un técnico válido.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de técnico</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <button id="toggle-btn" class="toggle-btn" aria-label="Abrir menú">☰</button>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="sidebar" id="sidebar" aria-label="Menú lateral">
        <button id="close-btn" class="close-btn" aria-label="Cerrar menú">✕</button>
        <nav class="sidebar-menu">
            <a href="admin-dashboard.php" class="sidebar-item">🏠 Dashboard</a>
            <a href="tecnicos.php" class="sidebar-item active">👨‍🔧 Técnicos</a>
            <a href="productos.php" class="sidebar-item">📦 Productos</a>
            <a href="reclamos.php" class="sidebar-item">⚠️ Reclamos</a>
            <a href="../php/logout.php" class="sidebar-item">🔒 Cerrar sesión</a>
        </nav>
    </aside>

    <div class="dashboard-container">
        <nav class="layout-navbar navbar">
            <div class="navbar-brand">Detalle de técnico</div>
            <div class="navbar-nav">
                <a href="../php/logout.php" class="btn btn-outline">Cerrar sesión</a>
            </div>
        </nav>

        <main class="layout-main">
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <?php if ($tecnico): ?>
                <section class="card">
                    <div class="card-header"><?= htmlspecialchars($tecnico['nombre']) ?></div>
                    <div class="card-body">
                        <p><strong>Correo:</strong> <?= htmlspecialchars($tecnico['correo']) ?></p>
                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($tecnico['telefono']) ?></p>
                        <p><strong>Ciudad:</strong> <?= htmlspecialchars($tecnico['ciudad'] ?? '-') ?></p>
                        <p><strong>País:</strong> <?= htmlspecialchars($tecnico['pais'] ?? '-') ?></p>
                        <p><strong>Dirección:</strong> <?= htmlspecialchars($tecnico['direccion'] ?? '-') ?></p>
                        <p><strong>Fecha de contratación:</strong> <?= htmlspecialchars($tecnico['fecha_contratacion'] ?? '-') ?></p>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="../js/hamburguer-menu.js"></script>
</body>
</html>
