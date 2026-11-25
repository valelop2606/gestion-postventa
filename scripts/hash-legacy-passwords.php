<?php
// One-time migration: hashes any password stored in plaintext.
// Usage (CLI only): php scripts/hash-legacy-passwords.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

require_once __DIR__ . '/../php/conexion.php';

$rows = $conexion->query('SELECT id_usuario, contrasena FROM usuario')->fetchAll(PDO::FETCH_ASSOC);
$update = $conexion->prepare('UPDATE usuario SET contrasena = :h WHERE id_usuario = :id');
$count = 0;

foreach ($rows as $row) {
    $info = password_get_info((string) $row['contrasena']);
    if ($info['algo'] === null || $info['algo'] === 0) {
        $update->execute(['h' => password_hash((string) $row['contrasena'], PASSWORD_DEFAULT), 'id' => $row['id_usuario']]);
        $count++;
    }
}

echo "Hashed {$count} legacy password(s)." . PHP_EOL;
