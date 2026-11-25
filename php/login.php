<?php
session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/render_view.php';

function passwordMatches(string $plainPassword, string $storedPassword): bool {
    // Only hashed passwords are accepted. Legacy plaintext rows must be migrated
    // with `php scripts/hash-legacy-passwords.php`.
    return password_verify($plainPassword, $storedPassword);
}

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    $stmt = $conexion->prepare('SELECT id_usuario, correo, contrasena, rol FROM usuario WHERE correo = :correo');
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && passwordMatches($contrasena, (string) $usuario['contrasena'])) {
        session_regenerate_id(true);

        if (password_needs_rehash((string) $usuario['contrasena'], PASSWORD_DEFAULT)) {
            $rehash = $conexion->prepare('UPDATE usuario SET contrasena = :h WHERE id_usuario = :id');
            $rehash->execute(['h' => password_hash($contrasena, PASSWORD_DEFAULT), 'id' => $usuario['id_usuario']]);
        }

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