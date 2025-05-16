<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../sql/bd.php';

// Obtener usuario actual
$id_usuario = $_SESSION['user_id'];

require __DIR__ . '/../includes/categorias_procesar.php';
require __DIR__ . '/../includes/ingreso.php';
require __DIR__ . '/../includes/cuentas_procesar.php';

// Obtener nombre del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

// Sumar saldo total de todas las cuentas
$stmt = $pdo->prepare("SELECT SUM(saldo_actual) AS total FROM cuentas WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$saldo = $stmt->fetch()['total'] ?? 0;

// Calcular total de ingresos
$stmt = $pdo->prepare("SELECT SUM(monto) AS total FROM movimientos WHERE id_usuario = ? AND tipo = 'ingreso'");
$stmt->execute([$id_usuario]);
$totalIngresos = $stmt->fetch()['total'] ?? 0;

// Calcular total de egresos
$stmt = $pdo->prepare("SELECT SUM(monto) AS total FROM movimientos WHERE id_usuario = ? AND tipo = 'egreso'");
$stmt->execute([$id_usuario]);
$totalEgresos = $stmt->fetch()['total'] ?? 0;

// Calcular porcentaje gastado
$porcentajeGasto = $totalIngresos > 0 ? round(($totalEgresos / $totalIngresos) * 100, 2) : 0;

// Obtener cuentas del usuario
$stmt = $pdo->prepare("SELECT id, nombre, saldo_actual FROM cuentas WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$cuentas = $stmt->fetchAll();

// Obtener categorías por tipo
$categorias = ['ingreso' => [], 'egreso' => []];
foreach (['ingreso', 'egreso'] as $tipo) {
    $stmt = $pdo->prepare("SELECT id, nombre FROM categorias WHERE id_usuario = ? AND tipo = ?");
    $stmt->execute([$id_usuario, $tipo]);
    $categorias[$tipo] = $stmt->fetchAll();
}

// Obtener últimos 5 movimientos
$stmt = $pdo->prepare("
    SELECT m.tipo, m.monto, m.descripcion, m.fecha, c.nombre AS cuenta
    FROM movimientos m
    JOIN cuentas c ON m.id_cuenta = c.id
    WHERE m.id_usuario = ?
    ORDER BY m.fecha DESC
    LIMIT 5
");
$stmt->execute([$id_usuario]);
$movimientos = $stmt->fetchAll();

// Obtener meta de ahorro activa
$stmt = $pdo->prepare("SELECT * FROM metas_ahorro WHERE id_usuario = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$id_usuario]);
$meta = $stmt->fetch();

// Procesar borrado de cuenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_cuenta'])) {
    // Eliminar movimientos
    $pdo->prepare("DELETE FROM movimientos WHERE id_usuario = ?")->execute([$id_usuario]);
    // Eliminar cuentas
    $pdo->prepare("DELETE FROM cuentas WHERE id_usuario = ?")->execute([$id_usuario]);
    // Eliminar metas de ahorro
    $pdo->prepare("DELETE FROM metas_ahorro WHERE id_usuario = ?")->execute([$id_usuario]);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - FinTrack</title>
    <link rel="icon" href="../assets//img/Logo icono.svg" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="logo">
            <img src="../assets/img/logo.png" alt="FinTrack">
        </div>
        <h2>Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h2>
        <div class="card cerrar-sesion">
            <div class="avatar-menu">
                <video src="../assets/video/Avatar.mp4" class="avatar" autoplay loop muted playsinline></video>
                <div class="menu-logout">
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>

                <form method="POST" style="display:inline;">
                    <button type="submit" name="borrar_cuenta" class="btn btn-cancel" onclick="return confirm('¿Estás seguro de borrar los datos de tu cuenta? Esta acción no se puede deshacer y perderas todos tus datos.')">
                        Borrar Datos
                    </button>
                </form>
            </div>
            
        </div>
</div>
    </header>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="container">

        <!-- Tarjeta resumen financiero -->
        <div class="card stats">
            <h3>Resumen Financiero</h3>
            <p><strong>Saldo disponible:</strong> $<?= number_format($saldo, 2, ',', '.') ?></p>
            <p><strong>Total ingresos:</strong> $<?= number_format($totalIngresos, 2, ',', '.') ?></p>
            <p><strong>Total egresos:</strong> $<?= number_format($totalEgresos, 2, ',', '.') ?></p>
            <p><strong>Porcentaje Gastado:</strong> <?= $porcentajeGasto ?>%</p>
        </div>

        <!-- Tarjeta de cuentas -->
        <div class="card">
            <h4>Tus cuentas:</h4>
            <ul>
                <?php foreach ($cuentas as $cuenta): ?>
                    <li><?= htmlspecialchars($cuenta['nombre']) ?>: $<?= number_format($cuenta['saldo_actual'], 2, ',', '.') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Tarjeta de movimientos -->
        <div class="card">
            <h4>Últimos movimientos:</h4>
            <ul>
                <?php foreach ($movimientos as $mov): ?>
                    <?php
                        $fechaFormateada = date('d/m/Y', strtotime($mov['fecha']));
                        $descripcion = trim($mov['descripcion']);
                    ?>
                    <li>
                        <?= $fechaFormateada ?> - <?= ucfirst($mov['tipo']) ?> en <?= htmlspecialchars($mov['cuenta']) ?>: $<?= number_format($mov['monto'], 2, ',', '.') ?>
                        <?= $descripcion !== '' ? '(' . htmlspecialchars($descripcion) . ')' : '' ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Tarjeta de metas -->
        <div class="card">
            <?php if ($meta): ?>
                <h4>Meta de ahorro activa: <?= htmlspecialchars($meta['nombre_meta']) ?></h4>
                <p>Progreso: $<?= number_format($meta['monto_actual'], 2, ',', '.') ?> / $<?= number_format($meta['monto_meta'], 2, ',', '.') ?></p>
            <?php else: ?>
                <p>No tienes metas de ahorro activas.</p>
            <?php endif; ?>
        </div>

        <!-- Formularios -->
        <div class="card formulario">
            <?php include __DIR__ . '/../componentes/formulario_movimiento.php'; ?>
            <?php include __DIR__ . '/../componentes/formulario_categoria.php'; ?>
            <?php include __DIR__ . '/../componentes/formulario_cuenta.php'; ?>
        </div>
    </div>
</body>
</html>