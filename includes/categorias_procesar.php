<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_categoria'])) {
    $nombre = trim($_POST['nombre']);
    $tipo = $_POST['tipo'];

    if ($nombre && in_array($tipo, ['ingreso', 'egreso'])) {
        $stmt = $pdo->prepare("INSERT INTO categorias (id_usuario, nombre, tipo) VALUES (?, ?, ?)");
        $stmt->execute([$id_usuario, $nombre, $tipo]);
        
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $mensaje = "⚠️ Datos inválidos para categoría.";
    }
}
?>