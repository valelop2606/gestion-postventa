<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];

    $stmt = $conexion->prepare("SELECT * FROM usuario WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch();

    if ($usuario && $contrasena === $usuario['contrasena']) {
        
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['correo'] = $usuario['correo'];

        if ($usuario['rol'] === 'admin') {
            header("Location: ../public/admin-dashboard.html");
            exit();
        } elseif ($usuario['rol'] === 'cliente') {
            $stmtCliente = $conexion->prepare("SELECT id_cliente, nombre FROM cliente WHERE id_usuario = :id");
            $stmtCliente->execute(['id' => $usuario['id_usuario']]);
            $cliente = $stmtCliente->fetch();
            
            $_SESSION['id_cliente'] = $cliente['id_cliente'];
            $_SESSION['nombre_cliente'] = $cliente['nombre'];

            header("Location: ../public/mis-productos.html");
            exit();
        }
    } else {
        header("Location: ../public/login.html?error=1");
        exit();
    }
}