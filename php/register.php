<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena']; 
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $ciudad = trim($_POST['ciudad']);
    $pais = trim($_POST['pais']);
    $direccion = trim($_POST['direccion']);

    try {
        $conexion->beginTransaction();

        $sqlUsuario = "INSERT INTO usuario (correo, contrasena, rol) VALUES (:correo, :contrasena, 'cliente')";
        $stmtUser = $conexion->prepare($sqlUsuario);
        $stmtUser->execute([
            'correo' => $correo,
            'contrasena' => $contrasena
        ]);

        $id_usuario_nuevo = $conexion->lastInsertId();

        $sqlCliente = "INSERT INTO cliente (nombre, telefono, ciudad, pais, direccion, id_usuario) 
                       VALUES (:nombre, :telefono, :ciudad, :pais, :direccion, :id_usuario)";
        $stmtClient = $conexion->prepare($sqlCliente);
        $stmtClient->execute([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'ciudad' => $ciudad,
            'pais' => $pais,
            'direccion' => $direccion,
            'id_usuario' => $id_usuario_nuevo
        ]);

        $conexion->commit();

        $_SESSION['id_usuario'] = $id_usuario_nuevo;
        $_SESSION['rol'] = 'cliente';
        $_SESSION['nombre_cliente'] = $nombre;

        header("Location: ../public/mis-productos.html");
        exit();

    } catch (Exception $e) {
        $conexion->rollBack();
        die("Error en el registro: " . $e->getMessage());
    }
}