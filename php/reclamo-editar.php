<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/reclamo_estado.php';

$idReclamo = (int) ($_GET['id'] ?? 0);
$reclamo = null;
$tecnicos = [];
$errorMessage = null;
$successMessage = null;

if ($idReclamo > 0) {
    try {
        $stmtReclamo = $conexion->prepare(
            "SELECT r.id_reclamo, r.descripcion_problema, r.estado, r.fecha_recepcion, r.fecha_revision, r.fecha_resolucion, r.id_tecnico, c.nombre AS cliente, p.nombre AS producto
             FROM reclamo r
             LEFT JOIN producto_cliente pc ON pc.id_producto_cliente = r.id_producto_cliente
             LEFT JOIN cliente c ON c.id_cliente = pc.id_cliente
             LEFT JOIN producto_catalogo p ON p.id_producto_catalogo = pc.id_producto_catalogo
             WHERE r.id_reclamo = :id"
        );
        $stmtReclamo->execute(['id' => $idReclamo]);
        $reclamo = $stmtReclamo->fetch(PDO::FETCH_ASSOC);

        $stmtTecnicos = $conexion->query("SELECT id_tecnico, nombre FROM tecnico ORDER BY nombre");
        $tecnicos = $stmtTecnicos->fetchAll(PDO::FETCH_ASSOC);

        if ($reclamo) {
            $reclamo['estado_normalizado'] = normalizar_estado_reclamo($reclamo['estado'] ?? 'pendiente');
        }
    } catch (PDOException $e) {
        $errorMessage = 'No se pudo cargar el reclamo. ' . $e->getMessage();
    }
} else {
    $errorMessage = 'No se indicó un reclamo válido.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idTecnico = (int) ($_POST['id_tecnico'] ?? 0);
    $estado = trim($_POST['estado'] ?? '');
    $fechaRevision = trim($_POST['fecha_revision'] ?? '');
    $fechaResolucion = trim($_POST['fecha_resolucion'] ?? '');

    try {
        $stmtUpdate = $conexion->prepare(
            "UPDATE reclamo SET id_tecnico = :id_tecnico, estado = :estado, fecha_revision = :fecha_revision, fecha_resolucion = :fecha_resolucion WHERE id_reclamo = :id_reclamo"
        );
        $stmtUpdate->execute([
            'id_tecnico' => $idTecnico > 0 ? $idTecnico : null,
            'estado' => normalizar_estado_reclamo($estado !== '' ? $estado : 'pendiente'),
            'fecha_revision' => $fechaRevision !== '' ? $fechaRevision : null,
            'fecha_resolucion' => $fechaResolucion !== '' ? $fechaResolucion : null,
            'id_reclamo' => $idReclamo,
        ]);
        $successMessage = 'Reclamo actualizado correctamente.';
    } catch (PDOException $e) {
        $errorMessage = 'No se pudo actualizar el reclamo. ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar reclamo</title>
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
            <a href="productos.php" class="sidebar-item">📦 Productos</a>
            <a href="reclamos.php" class="sidebar-item active">⚠️ Reclamos</a>
            <a href="../php/logout.php" class="sidebar-item">🔒 Cerrar sesión</a>
        </nav>
    </aside>

    <div class="dashboard-container">
        <nav class="layout-navbar navbar">
            <div class="navbar-brand">Editar reclamo</div>
            <div class="navbar-nav">
                <a href="../php/logout.php" class="btn btn-outline">Cerrar sesión</a>
            </div>
        </nav>

        <main class="layout-main">
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($reclamo): ?>
                <section class="card">
                    <div class="card-header">Reclamo #<?= (int) $reclamo['id_reclamo'] ?></div>
                    <div class="card-body">
                        <p><strong>Cliente:</strong> <?= htmlspecialchars($reclamo['cliente'] ?? '-') ?></p>
                        <p><strong>Producto:</strong> <?= htmlspecialchars($reclamo['producto'] ?? '-') ?></p>
                        <p><strong>Descripción:</strong> <?= htmlspecialchars($reclamo['descripcion_problema'] ?? '-') ?></p>
                        <p><strong>Estado actual:</strong> <?= htmlspecialchars(estadoTexto($reclamo['estado_normalizado'] ?? $reclamo['estado'] ?? 'pendiente')) ?></p>

                        <form method="POST" class="form-grid" style="margin-top: 1.5rem;">
                            <div class="form-group">
                                <label class="label" for="id_tecnico">Técnico asignado</label>
                                <select id="id_tecnico" name="id_tecnico" class="input">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?= (int) $tecnico['id_tecnico'] ?>" <?= ((int) ($reclamo['id_tecnico'] ?? 0) === (int) $tecnico['id_tecnico']) ? 'selected' : '' ?>><?= htmlspecialchars($tecnico['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="label" for="estado">Estado</label>
                                <select id="estado" name="estado" class="input">
                                    <option value="pendiente" <?= (($reclamo['estado_normalizado'] ?? '') === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="asignado" <?= (($reclamo['estado_normalizado'] ?? '') === 'asignado') ? 'selected' : '' ?>>Asignado</option>
                                    <option value="en_revision" <?= (($reclamo['estado_normalizado'] ?? '') === 'en_revision') ? 'selected' : '' ?>>En revisión</option>
                                    <option value="resuelto" <?= (($reclamo['estado_normalizado'] ?? '') === 'resuelto') ? 'selected' : '' ?>>Resuelto</option>
                                    <option value="rechazado" <?= (($reclamo['estado_normalizado'] ?? '') === 'rechazado') ? 'selected' : '' ?>>Rechazado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="label" for="fecha_revision">Fecha de revisión</label>
                                <input type="date" id="fecha_revision" name="fecha_revision" class="input" value="<?= htmlspecialchars($reclamo['fecha_revision'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="label" for="fecha_resolucion">Fecha de resolución</label>
                                <input type="date" id="fecha_resolucion" name="fecha_resolucion" class="input" value="<?= htmlspecialchars($reclamo['fecha_resolucion'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="../js/hamburguer-menu.js"></script>
</body>
</html>
