<?php
session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = (string) ($_POST['contrasena'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($correo === '' || $contrasena === '' || $nombre === '' || $telefono === '') {
        $errorMessage = 'Completa los campos obligatorios.';
    } else {
        try {
            $stmtUsuario = $conexion->prepare('SELECT 1 FROM usuario WHERE correo = :correo');
            $stmtUsuario->execute(['correo' => $correo]);

            if ($stmtUsuario->fetchColumn()) {
                $errorMessage = 'Ya existe una cuenta registrada con ese correo.';
            } else {
                $conexion->beginTransaction();

                $stmtUser = $conexion->prepare('INSERT INTO usuario (correo, contrasena, rol) VALUES (:correo, :contrasena, :rol)');
                $stmtUser->execute([
                    'correo' => $correo,
                    'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),
                    'rol' => 'cliente',
                ]);

                $idUsuarioNuevo = (int) $conexion->lastInsertId();

                $stmtClient = $conexion->prepare('INSERT INTO cliente (nombre, telefono, ciudad, pais, direccion, id_usuario) VALUES (:nombre, :telefono, :ciudad, :pais, :direccion, :id_usuario)');
                $stmtClient->execute([
                    'nombre' => $nombre,
                    'telefono' => $telefono,
                    'ciudad' => $ciudad !== '' ? $ciudad : null,
                    'pais' => $pais !== '' ? $pais : null,
                    'direccion' => $direccion !== '' ? $direccion : null,
                    'id_usuario' => $idUsuarioNuevo,
                ]);

                $idClienteNuevo = (int) $conexion->lastInsertId();
                $conexion->commit();

                $_SESSION['id_usuario'] = $idUsuarioNuevo;
                $_SESSION['rol'] = 'cliente';
                $_SESSION['correo'] = $correo;
                $_SESSION['id_cliente'] = $idClienteNuevo;
                $_SESSION['nombre_cliente'] = $nombre;

                header('Location: dashboard-cliente.php');
                exit();
            }
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $errorMessage = 'No se pudo completar el registro. ' . $e->getMessage();
        }
    }
}

render_view('register.html', [
    'errorMessage' => $errorMessage,
]);