<?php
session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';

function passwordMatches(string $plainPassword, string $storedPassword): bool {
    if (password_verify($plainPassword, $storedPassword)) {
        return true;
    }

    return hash_equals($storedPassword, $plainPassword);
}

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    $stmt = $conexion->prepare('SELECT id_usuario, correo, contrasena, rol FROM usuario WHERE correo = :correo');
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && passwordMatches($contrasena, (string) $usuario['contrasena'])) {
        $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['correo'] = $usuario['correo'];

        if ($usuario['rol'] === 'admin') {
            header('Location: admin-dashboard.php');
            exit();
        }

        if ($usuario['rol'] === 'cliente') {
            $stmtCliente = $conexion->prepare('SELECT id_cliente, nombre FROM cliente WHERE id_usuario = :id_usuario');
            $stmtCliente->execute(['id_usuario' => $usuario['id_usuario']]);
            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $_SESSION['id_cliente'] = (int) $cliente['id_cliente'];
                $_SESSION['nombre_cliente'] = $cliente['nombre'];

                header('Location: dashboard-cliente.php');
                exit();
            }

            $errorMessage = 'Tu cuenta no tiene un perfil de cliente asociado.';
        } else {
            $errorMessage = 'Tu cuenta no tiene permiso para acceder a esta plataforma.';
        }
    } else {
        $errorMessage = 'Correo o contraseña incorrectos.';
    }
}

render_view('login.html', [
    'errorMessage' => $errorMessage,
]);