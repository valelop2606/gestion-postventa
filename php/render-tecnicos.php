<?php
session_start();

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';

$tecnicos = [];
$errorMessage = null;

try {
    $stmt = $conexion->query(
        "SELECT id_tecnico, nombre, correo, telefono, ciudad FROM tecnico ORDER BY nombre"
    );
    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'No se pudo cargar la lista de técnicos. ' . $e->getMessage();
}

render_view('tecnicos.html', [
    'tecnicos' => $tecnicos,
    'errorMessage' => $errorMessage,
]);
