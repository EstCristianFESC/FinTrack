<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../sql/bd.php';

$id_usuario = $_SESSION['user_id'];
$mensaje = "";

// Crear categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $tipo = $_POST['tipo'];

    if ($nombre && in_array($tipo, ['ingreso', 'egreso'])) {
        $stmt = $pdo->prepare("INSERT INTO categorias (id_usuario, nombre, tipo) VALUES (?, ?, ?)");
        $stmt->execute([$id_usuario, $nombre, $tipo]);
        $mensaje = "✅ Categoría creada correctamente.";
    } else {
        $mensaje = "⚠️ Datos inválidos.";
    }
}

// Eliminar categoría (solo si no está en movimientos)
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM movimientos WHERE id_categoria = ? AND id_usuario = ?");
    $stmt->execute([$id, $id_usuario]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id, $id_usuario]);
        $mensaje = "🗑️ Categoría eliminada.";
    } else {
        $mensaje = "❌ No puedes eliminar una categoría en uso.";
    }
}

// Obtener categorías
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id_usuario = ? ORDER BY tipo, nombre");
$stmt->execute([$id_usuario]);
$categorias = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Categorías - FinTrack</title>
</head>
<body>
    <h2>Administrar categorías</h2>

    <?php if ($mensaje): ?>
        <p style="color: green;"><?= $mensaje ?></p>
    <?php endif; ?>

    <h3>Crear nueva categoría</h3>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre de categoría" required>
        <select name="tipo">
            <option value="ingreso">Ingreso</option>
            <option value="egreso">Egreso</option>
        </select>
        <button type="submit" name="crear">Crear</button>
    </form>

    <h3>Categorías existentes</h3>
    <?php foreach (['ingreso', 'egreso'] as $tipo): ?>
        <h4><?= ucfirst($tipo) ?>s</h4>
        <ul>
            <?php foreach ($categorias[$tipo] ?? [] as $cat): ?>
                <li>
                    <?= htmlspecialchars($cat['nombre']) ?>
                    <a href="?eliminar=<?= $cat['id'] ?>" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</body>
</html>