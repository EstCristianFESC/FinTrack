<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_cuenta'])) {
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $saldo = floatval($_POST['saldo']);

    if ($nombre) {
        $stmt = $pdo->prepare("INSERT INTO cuentas (id_usuario, nombre, tipo, saldo_actual) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_usuario, $nombre, $tipo, $saldo]);

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $mensaje = "⚠️ El nombre de la cuenta es obligatorio.";
    }
}
?>